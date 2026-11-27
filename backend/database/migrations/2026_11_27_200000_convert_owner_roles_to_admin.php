<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CONVERSIONS_TABLE = 'role_owner_to_admin_user_ids';

    private const STATE_TABLE = 'role_owner_to_admin_state';

    public function up(): void
    {
        if ($this->isUpComplete()) {
            $this->enableSqliteForeignKeys();

            return;
        }

        $this->runSafely(function (): void {
            $this->recoverSqliteRebuild();
            $this->assertUsersTableExists();
            $this->ensureUsersEmailIndex();

            $currentRoles = $this->allowedRoles();

            if (! Schema::hasTable(self::STATE_TABLE)) {
                $previousRoles = $this->inferPreviousRoles($currentRoles);

                Schema::create(self::STATE_TABLE, function (Blueprint $table) {
                    $table->unsignedTinyInteger('id')->primary();
                    $table->text('previous_roles');
                });

                DB::table(self::STATE_TABLE)->insert([
                    'id' => 1,
                    'previous_roles' => json_encode($previousRoles, JSON_THROW_ON_ERROR),
                ]);
            }

            if (! Schema::hasTable(self::CONVERSIONS_TABLE)) {
                Schema::create(self::CONVERSIONS_TABLE, function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id')->primary();
                });
            }

            $this->setAllowedRoles(['kasir', 'owner', 'admin']);

            DB::table(self::CONVERSIONS_TABLE)->insertUsing(
                ['user_id'],
                DB::table('users')
                    ->select('id')
                    ->where('role', 'owner')
                    ->whereNotIn('id', function ($query) {
                        $query->select('user_id')->from(self::CONVERSIONS_TABLE);
                    })
            );

            DB::table('users')
                ->where('role', 'owner')
                ->update(['role' => 'admin']);

            $this->setAllowedRoles(['kasir', 'admin']);

            if (DB::table('users')->where('role', 'owner')->exists()) {
                throw new RuntimeException('Owner roles remain after conversion.');
            }
        });
    }

    public function down(): void
    {
        if ($this->isDownComplete()) {
            $this->enableSqliteForeignKeys();

            return;
        }

        if (! Schema::hasTable(self::CONVERSIONS_TABLE) || ! Schema::hasTable(self::STATE_TABLE)) {
            throw new RuntimeException('Role conversion metadata is missing; rollback cannot be performed safely.');
        }

        $this->recoverIncompleteSqliteUsersTable();

        $untrackedAdminsExist = DB::table('users')
            ->where('role', 'admin')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from(self::CONVERSIONS_TABLE);
            })
            ->exists();

        if ($untrackedAdminsExist) {
            throw new RuntimeException(
                'Cannot roll back owner-to-admin conversion while unconverted admin users exist.'
            );
        }

        $previousRoles = $this->previousRoles();

        $this->runSafely(function () use ($previousRoles): void {
            $this->recoverSqliteRebuild();
            $this->assertUsersTableExists();
            $this->ensureUsersEmailIndex();

            $this->setAllowedRoles(['kasir', 'owner', 'admin']);

            DB::table('users')
                ->whereIn('id', function ($query) {
                    $query->select('user_id')->from(self::CONVERSIONS_TABLE);
                })
                ->where('role', 'admin')
                ->update(['role' => 'owner']);

            $this->setAllowedRoles($previousRoles);

            Schema::drop(self::CONVERSIONS_TABLE);
            Schema::drop(self::STATE_TABLE);
        });
    }

    private function runSafely(callable $callback): void
    {
        $connection = DB::connection();

        if ($connection->getDriverName() !== 'sqlite') {
            $callback();

            return;
        }

        // SQLite ignores foreign-key PRAGMA changes inside transactions.
        try {
            $connection->statement('PRAGMA foreign_keys = OFF');

            if ((int) $connection->scalar('PRAGMA foreign_keys') !== 0) {
                throw new RuntimeException('Could not disable SQLite foreign keys before rebuilding users.');
            }

            $connection->transaction(function () use ($callback, $connection): void {
                $callback();

                if ($connection->select('PRAGMA foreign_key_check') !== []) {
                    throw new RuntimeException('SQLite foreign key validation failed during role conversion.');
                }
            });
        } finally {
            $this->enableSqliteForeignKeys();
        }
    }

    private function enableSqliteForeignKeys(): void
    {
        $connection = DB::connection();

        if ($connection->getDriverName() !== 'sqlite') {
            return;
        }

        try {
            $connection->statement('PRAGMA foreign_keys = ON');
        } catch (Throwable $exception) {
            try {
                $connection->getPdo()->exec('PRAGMA foreign_keys = ON');
            } catch (Throwable $recoveryException) {
                throw new RuntimeException(
                    'SQLite foreign key enforcement could not be enabled.',
                    0,
                    $recoveryException
                );
            }
        }

        if ((int) $connection->scalar('PRAGMA foreign_keys') !== 1) {
            throw new RuntimeException('SQLite foreign key enforcement could not be enabled.');
        }
    }

    private function recoverSqliteRebuild(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        $connection = DB::connection();
        $prefix = $connection->getTablePrefix();
        $usersTable = $prefix.'users';
        $temporaryTable = '__temp__'.$prefix.'users';

        $usersExists = $this->sqliteTableExists($usersTable);
        $temporaryTableExists = $this->sqliteTableExists($temporaryTable);

        if (! $usersExists && ! $temporaryTableExists) {
            throw new RuntimeException('Neither users nor its SQLite rebuild table exists.');
        }

        if ($temporaryTableExists && ! $this->sqliteTableHasColumns($temporaryTable, ['id', 'email', 'role'])) {
            throw new RuntimeException('The SQLite users rebuild table has an unexpected structure.');
        }

        if ($usersExists && $temporaryTableExists) {
            $connection->statement(
                'DROP TABLE '.$connection->getQueryGrammar()->wrapTable($temporaryTable, '')
            );
        } elseif (! $usersExists) {
            $connection->statement(
                'ALTER TABLE '.$connection->getQueryGrammar()->wrapTable($temporaryTable, '')
                .' RENAME TO '.$connection->getQueryGrammar()->wrapTable($usersTable, '')
            );
        }
    }

    private function ensureUsersEmailIndex(): void
    {
        if (! Schema::hasIndex('users', 'users_email_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('email', 'users_email_unique');
            });
        }
    }

    private function assertUsersTableExists(): void
    {
        if (! Schema::hasTable('users')) {
            throw new RuntimeException('The users table is unavailable for role conversion.');
        }
    }

    private function sqliteTableExists(string $table): bool
    {
        return DB::connection()->selectOne(
            'SELECT 1 FROM sqlite_master WHERE type = ? AND name = ?',
            ['table', $table]
        ) !== null;
    }

    private function sqliteTableHasColumns(string $table, array $expected): bool
    {
        $quotedTable = '"'.str_replace('"', '""', $table).'"';
        $columns = DB::connection()->select('PRAGMA table_info('.$quotedTable.')');
        $names = array_map(fn (object $column): string => $column->name, $columns);

        return array_diff($expected, $names) === [];
    }

    private function recoverIncompleteSqliteUsersTable(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        if (Schema::hasTable('users') && ! $this->sqliteTemporaryUsersTableExists()) {
            return;
        }

        $this->runSafely(function (): void {
            $this->recoverSqliteRebuild();
            $this->assertUsersTableExists();
            $this->ensureUsersEmailIndex();
        });
    }

    private function isUpComplete(): bool
    {
        if (! Schema::hasTable('users')
            || ! Schema::hasTable(self::CONVERSIONS_TABLE)
            || ! Schema::hasTable(self::STATE_TABLE)
            || $this->sqliteTemporaryUsersTableExists()) {
            return false;
        }

        return $this->sameRoles($this->allowedRoles(), ['kasir', 'admin'])
            && ! DB::table('users')->where('role', 'owner')->exists();
    }

    private function isDownComplete(): bool
    {
        if (! Schema::hasTable('users')
            || Schema::hasTable(self::CONVERSIONS_TABLE)
            || Schema::hasTable(self::STATE_TABLE)
            || $this->sqliteTemporaryUsersTableExists()) {
            return false;
        }

        $roles = $this->allowedRoles();

        return $this->sameRoles($roles, ['kasir', 'owner'])
            || $this->sameRoles($roles, ['kasir', 'admin'])
            || $this->sameRoles($roles, ['kasir', 'owner', 'admin']);
    }

    private function sqliteTemporaryUsersTableExists(): bool
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return false;
        }

        return $this->sqliteTableExists(
            '__temp__'.DB::connection()->getTablePrefix().'users'
        );
    }

    private function allowedRoles(): array
    {
        $connection = DB::connection();

        if ($connection->getDriverName() === 'sqlite') {
            $table = $connection->getTablePrefix().'users';
            $definition = $connection->selectOne(
                'SELECT sql FROM sqlite_master WHERE type = ? AND name = ?',
                ['table', $table]
            )?->sql;

            if (! $definition
                || ! preg_match('/check\s*\(\s*"?role"?\s+in\s*\(([^)]*)\)/i', $definition, $matches)) {
                throw new RuntimeException('Could not read the SQLite users.role constraint.');
            }

            preg_match_all("/'((?:''|[^'])*)'/", $matches[1], $roleMatches);

            return $this->sortRoles(array_map(
                fn (string $role): string => str_replace("''", "'", $role),
                $roleMatches[1]
            ));
        }

        if (in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            $column = $connection->selectOne(
                'SELECT COLUMN_TYPE AS type FROM information_schema.columns '
                .'WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
                [$connection->getTablePrefix().'users', 'role']
            );

            if (! $column || ! preg_match('/^enum\((.*)\)$/i', $column->type, $matches)) {
                throw new RuntimeException('Could not read the MySQL users.role enum.');
            }

            preg_match_all("/'((?:''|[^'])*)'/", $matches[1], $roleMatches);

            return $this->sortRoles(array_map(
                fn (string $role): string => str_replace("''", "'", $role),
                $roleMatches[1]
            ));
        }

        throw new RuntimeException('Role conversion is supported only for SQLite and MySQL.');
    }

    private function inferPreviousRoles(array $currentRoles): array
    {
        if (Schema::hasTable(self::CONVERSIONS_TABLE)
            && DB::table(self::CONVERSIONS_TABLE)->exists()) {
            return ['kasir', 'owner'];
        }

        if (in_array('owner', $currentRoles, true)
            && DB::table('users')->where('role', 'owner')->exists()) {
            return ['kasir', 'owner'];
        }

        return $currentRoles;
    }

    private function previousRoles(): array
    {
        $state = DB::table(self::STATE_TABLE)->where('id', 1)->value('previous_roles');
        $roles = json_decode($state ?? '', true);

        if (! is_array($roles)
            || (! $this->sameRoles($roles, ['kasir', 'owner'])
                && ! $this->sameRoles($roles, ['kasir', 'admin'])
                && ! $this->sameRoles($roles, ['kasir', 'owner', 'admin']))) {
            throw new RuntimeException('Stored role conversion state is invalid.');
        }

        return $this->sortRoles($roles);
    }

    private function setAllowedRoles(array $roles): void
    {
        $roles = $this->sortRoles($roles);

        if ($this->sameRoles($this->allowedRoles(), $roles)) {
            return;
        }

        if (array_diff($roles, ['kasir', 'owner', 'admin']) !== []) {
            throw new RuntimeException('The users.role constraint contains unsupported role values.');
        }

        Schema::table('users', function (Blueprint $table) use ($roles) {
            $table->enum('role', $roles)->default('kasir')->change();
        });
    }

    private function sameRoles(array $first, array $second): bool
    {
        return $this->sortRoles($first) === $this->sortRoles($second);
    }

    private function sortRoles(array $roles): array
    {
        $roles = array_values(array_unique($roles));
        sort($roles);

        return $roles;
    }
};
