<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warkop Sukses — Pesan Menu Online</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        warkop: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-amber-50/40 text-stone-800 min-h-screen flex flex-col antialiased">

    <!-- NAVBAR -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-amber-200/70 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-600 text-white flex items-center justify-center font-black text-xl shadow-md shadow-amber-600/20">
                    ☕
                </div>
                <div>
                    <h1 class="font-extrabold text-lg leading-tight text-stone-900">Warkop Sukses</h1>
                    <p class="text-xs text-stone-500 font-medium">Pesan dari meja tanpa antre</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button onclick="openTrackerModal()" class="text-xs font-semibold px-3 py-2 rounded-xl text-amber-800 bg-amber-100/80 hover:bg-amber-200/80 transition flex items-center gap-1.5">
                    <span>🔍</span>
                    <span class="hidden sm:inline">Lacak Pesanan</span>
                </button>
                <button onclick="openCartModal()" class="relative text-xs font-bold px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white shadow-md shadow-amber-600/20 transition flex items-center gap-2">
                    <span>🛒</span>
                    <span class="hidden sm:inline">Keranjang</span>
                    <span id="cartCountBadge" class="bg-white text-amber-800 text-[10px] font-black px-1.5 py-0.5 rounded-full hidden">0</span>
                </button>
            </div>
        </div>
    </header>

    <!-- HERO BANNER -->
    <section class="max-w-5xl mx-auto px-4 pt-6 pb-2 w-full">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-700 via-amber-800 to-stone-900 text-white p-6 sm:p-8 shadow-xl">
            <div class="relative z-10 max-w-lg">
                <span class="inline-block px-3 py-1 rounded-full bg-amber-500/20 border border-amber-400/30 text-amber-200 text-xs font-semibold mb-3">
                    ✨ Web Pemesanan Pelanggan
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-2 leading-snug">
                    Ngopi Santai, Pesan Langsung dari HP-mu!
                </h2>
                <p class="text-amber-100/80 text-sm mb-4 leading-relaxed">
                    Pilih menu favorit, tulis nomormu/mejamu, lalu tunggu pesanan diantar oleh barista & kasir kami.
                </p>
                <div class="flex flex-wrap gap-2 text-xs">
                    <span class="bg-black/30 backdrop-blur-sm px-3 py-1.5 rounded-xl border border-white/10">⚡ Cepat & Praktis</span>
                    <span class="bg-black/30 backdrop-blur-sm px-3 py-1.5 rounded-xl border border-white/10">💵 Bayar Tunai / QRIS</span>
                    <span class="bg-black/30 backdrop-blur-sm px-3 py-1.5 rounded-xl border border-white/10">📱 Terhubung ke Aplikasi Kasir</span>
                </div>
            </div>
            <!-- Decorative circle -->
            <div class="absolute -right-8 -bottom-10 w-48 h-48 bg-amber-500/20 rounded-full blur-2xl pointer-events-none"></div>
        </div>
    </section>

    <!-- SEARCH & CATEGORY BAR -->
    <section class="max-w-5xl mx-auto px-4 py-4 w-full flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
        <div class="relative flex-1">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400">🔍</span>
            <input 
                id="searchMenuInput" 
                type="text" 
                placeholder="Cari kopi, mie, gorengan..." 
                oninput="filterMenu()"
                class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-stone-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition shadow-sm"
            >
        </div>
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 text-xs font-semibold">
            <button onclick="setCategory('all')" id="btnCatAll" class="category-btn active px-4 py-2 rounded-xl bg-amber-600 text-white shadow-sm transition">Semua</button>
            <button onclick="setCategory('minuman')" id="btnCatMinuman" class="category-btn px-4 py-2 rounded-xl bg-white border border-stone-200 text-stone-600 hover:bg-stone-50 transition">Minuman</button>
            <button onclick="setCategory('makanan')" id="btnCatMakanan" class="category-btn px-4 py-2 rounded-xl bg-white border border-stone-200 text-stone-600 hover:bg-stone-50 transition">Makanan</button>
        </div>
    </section>

    <!-- MENU LIST SECTION -->
    <main class="max-w-5xl mx-auto px-4 pb-24 w-full flex-1">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-stone-900 text-base flex items-center gap-2">
                <span>Daftar Menu</span>
                <span id="menuCountBadge" class="text-xs font-normal text-stone-500 bg-stone-200/70 px-2 py-0.5 rounded-full">Memuat...</span>
            </h3>
        </div>

        <!-- Grid Container -->
        <div id="menuGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5 sm:gap-4">
            <!-- Loading Skeleton -->
            <div class="animate-pulse bg-white p-4 rounded-3xl border border-stone-100 shadow-sm flex flex-col justify-between h-48">
                <div class="w-12 h-12 bg-amber-100 rounded-2xl mb-3"></div>
                <div class="h-4 bg-stone-200 rounded w-3/4 mb-2"></div>
                <div class="h-3 bg-stone-100 rounded w-1/2"></div>
                <div class="h-8 bg-stone-200 rounded-xl mt-4"></div>
            </div>
            <div class="animate-pulse bg-white p-4 rounded-3xl border border-stone-100 shadow-sm flex flex-col justify-between h-48">
                <div class="w-12 h-12 bg-amber-100 rounded-2xl mb-3"></div>
                <div class="h-4 bg-stone-200 rounded w-3/4 mb-2"></div>
                <div class="h-3 bg-stone-100 rounded w-1/2"></div>
                <div class="h-8 bg-stone-200 rounded-xl mt-4"></div>
            </div>
        </div>

        <!-- Empty State -->
        <div id="emptyMenuState" class="hidden text-center py-16 bg-white rounded-3xl border border-dashed border-stone-200 p-8 my-6">
            <div class="text-4xl mb-2">☕</div>
            <p class="font-bold text-stone-700 text-sm">Menu tidak ditemukan</p>
            <p class="text-xs text-stone-400 mt-1">Coba cari dengan kata kunci lain.</p>
        </div>
    </main>

    <!-- FLOATING CART BAR (Mobile only when items exist) -->
    <div id="floatingCartBar" class="fixed bottom-4 inset-x-4 max-w-md mx-auto z-30 transition transform translate-y-32">
        <button onclick="openCartModal()" class="w-full bg-amber-600 hover:bg-amber-700 text-white p-3.5 rounded-2xl shadow-xl shadow-amber-600/30 flex items-center justify-between font-bold text-sm transition">
            <div class="flex items-center gap-2.5">
                <span class="bg-amber-800 text-white text-xs px-2 py-0.5 rounded-lg font-black" id="floatQty">0</span>
                <span>Lihat Keranjang</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span id="floatTotal">Rp 0</span>
                <span>➔</span>
            </div>
        </button>
    </div>

    <!-- CART & CHECKOUT MODAL -->
    <div id="cartModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white w-full max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden animate-in fade-in duration-200">
            <!-- Modal Header -->
            <div class="p-4 sm:p-5 border-b border-stone-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🛒</span>
                    <h3 class="font-extrabold text-stone-900 text-base">Keranjang Pesanan</h3>
                </div>
                <button onclick="closeCartModal()" class="w-8 h-8 rounded-full bg-stone-100 text-stone-500 hover:bg-stone-200 flex items-center justify-center text-sm font-bold">✕</button>
            </div>

            <!-- Cart Items List -->
            <div id="cartItemsContainer" class="p-4 sm:p-5 overflow-y-auto flex-1 divide-y divide-stone-100">
                <!-- Injected via JS -->
            </div>

            <!-- Checkout Form Section -->
            <div id="checkoutFormSection" class="p-4 sm:p-5 bg-stone-50 border-t border-stone-200/80">
                <div class="space-y-3 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Nama Pemesan / No. Meja <span class="text-rose-500">*</span></label>
                        <input 
                            id="customerNameInput" 
                            type="text" 
                            placeholder="Contoh: Mas Budi - Meja 4" 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-500"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1">Metode Pembayaran <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-stone-200 bg-white cursor-pointer has-[:checked]:border-amber-600 has-[:checked]:bg-amber-50/50">
                                <input type="radio" name="paymentMethod" value="cash" checked class="text-amber-600 focus:ring-amber-500">
                                <span>💵 Tunai (Kasir)</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-stone-200 bg-white cursor-pointer has-[:checked]:border-amber-600 has-[:checked]:bg-amber-50/50">
                                <input type="radio" name="paymentMethod" value="qris" class="text-amber-600 focus:ring-amber-500">
                                <span>📱 QRIS</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm mb-4">
                    <span class="text-stone-500 font-medium">Total Pembayaran:</span>
                    <span id="cartTotalPrice" class="font-extrabold text-lg text-amber-700">Rp 0</span>
                </div>

                <button 
                    id="submitOrderBtn" 
                    onclick="submitOrder()" 
                    class="w-full py-3.5 bg-amber-600 hover:bg-amber-700 text-white rounded-2xl font-bold text-sm shadow-lg shadow-amber-600/25 transition flex items-center justify-center gap-2 disabled:opacity-50"
                >
                    <span>Kirim Pesanan Sekarang</span>
                </button>
            </div>
        </div>
    </div>

    <!-- TRACK ORDER / STATUS MODAL -->
    <div id="trackerModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden flex flex-col animate-in fade-in duration-200">
            <div class="p-4 border-b border-stone-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🔍</span>
                    <h3 class="font-extrabold text-stone-900 text-base">Cek Status Pesanan</h3>
                </div>
                <button onclick="closeTrackerModal()" class="w-8 h-8 rounded-full bg-stone-100 text-stone-500 hover:bg-stone-200 flex items-center justify-center text-sm font-bold">✕</button>
            </div>

            <div class="p-5">
                <div class="flex gap-2 mb-4">
                    <input 
                        id="trackCodeInput" 
                        type="text" 
                        placeholder="Masukkan Kode (cth: WS-ABC123)" 
                        class="flex-1 px-3.5 py-2.5 rounded-xl border border-stone-200 text-sm uppercase font-mono font-bold tracking-wider focus:outline-none focus:ring-2 focus:ring-amber-500"
                    >
                    <button onclick="trackOrderManual()" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition">
                        Cek
                    </button>
                </div>

                <!-- Result Box -->
                <div id="trackResultBox" class="hidden rounded-2xl border border-stone-200 p-4 bg-stone-50 text-xs">
                    <!-- Dynamic via JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- SUCCESS ORDER POPUP MODAL -->
    <div id="successModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl p-6 text-center animate-in zoom-in-95 duration-200">
            <div class="w-14 h-14 bg-emerald-100 text-emerald-600 text-2xl rounded-2xl mx-auto flex items-center justify-center mb-4">
                🎉
            </div>
            <h3 class="font-extrabold text-lg text-stone-900 mb-1">Pesanan Terkirim!</h3>
            <p class="text-xs text-stone-500 mb-4">Pesananmu sudah masuk ke aplikasi kasir warkop.</p>

            <div class="bg-amber-50 border border-amber-200/80 rounded-2xl p-3.5 mb-4 text-left">
                <div class="text-[10px] text-amber-700 uppercase font-black tracking-wider mb-1">Kode Pesanan Kamu:</div>
                <div class="flex items-center justify-between">
                    <span id="successOrderCode" class="font-mono text-xl font-extrabold text-amber-900 tracking-wider">WS-XXXXXX</span>
                    <button onclick="copyOrderCode()" class="text-xs font-bold text-amber-700 hover:text-amber-800 bg-amber-200/70 px-2 py-1 rounded-lg">Salin</button>
                </div>
            </div>

            <p class="text-[11px] text-stone-400 mb-5">Simpan kode ini untuk melihat status atau konfirmasi saat membayar ke kasir.</p>

            <div class="flex gap-2">
                <button onclick="viewActiveOrderFromSuccess()" class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition">
                    Pantau Status
                </button>
                <button onclick="closeSuccessModal()" class="flex-1 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-xl text-xs font-bold transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        // State
        let allProducts = [];
        let filteredProducts = [];
        let cart = {}; // { productId: { product, quantity } }
        let currentCategory = 'all';
        let pollingInterval = null;
        let lastCreatedCode = null;

        // Init
        document.addEventListener('DOMContentLoaded', () => {
            fetchMenu();
            loadCartFromStorage();
        });

        // 1. Fetch Menu from Backend API
        async function fetchMenu() {
            try {
                const res = await fetch('/api/v1/public/menu');
                const json = await res.json();
                if (json.status === 'success') {
                    allProducts = json.data;
                    filterMenu();
                } else {
                    showToast('Gagal memuat menu');
                }
            } catch (err) {
                console.error(err);
                document.getElementById('menuGrid').innerHTML = `
                    <div class="col-span-full text-center py-12 text-rose-500 font-semibold text-xs">
                        Gagal terhubung ke server backend. Pastikan server aktif.
                    </div>
                `;
            }
        }

        // 2. Render Menu Grid
        function renderMenu(products) {
            const grid = document.getElementById('menuGrid');
            const empty = document.getElementById('emptyMenuState');
            const badge = document.getElementById('menuCountBadge');

            badge.innerText = `${products.length} menu`;

            if (products.length === 0) {
                grid.innerHTML = '';
                empty.classList.remove('hidden');
                return;
            }

            empty.classList.add('hidden');
            grid.innerHTML = products.map(p => {
                const isDrink = p.name.toLowerCase().includes('kopi') || p.name.toLowerCase().includes('teh') || p.name.toLowerCase().includes('jeruk') || p.name.toLowerCase().includes('es');
                const emoji = isDrink ? '☕' : (p.name.toLowerCase().includes('mie') ? '🍜' : (p.name.toLowerCase().includes('nasi') ? '🍚' : '🍞'));
                const inCartQty = cart[p.id] ? cart[p.id].quantity : 0;

                return `
                    <div class="bg-white rounded-3xl p-4 border border-stone-200/80 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-amber-100/70 text-2xl flex items-center justify-center mb-3">
                                ${emoji}
                            </div>
                            <h4 class="font-bold text-stone-900 text-sm leading-snug line-clamp-2">${p.name}</h4>
                            <p class="text-amber-800 font-extrabold text-sm mt-1">Rp ${p.price.toLocaleString('id-ID')}</p>
                            
                            <div class="mt-2 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full ${p.available ? 'bg-emerald-500' : 'bg-stone-300'}"></span>
                                <span class="text-[11px] ${p.available ? 'text-emerald-700 font-semibold' : 'text-stone-400'}">
                                    ${p.available ? 'Tersedia' : 'Habis'}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4">
                            ${p.available ? `
                                ${inCartQty > 0 ? `
                                    <div class="flex items-center justify-between bg-amber-50 rounded-xl p-1 border border-amber-200">
                                        <button onclick="changeQty(${p.id}, -1)" class="w-7 h-7 bg-white text-amber-800 rounded-lg font-black text-sm shadow-xs hover:bg-amber-100 flex items-center justify-center">-</button>
                                        <span class="text-xs font-black text-amber-900">${inCartQty}</span>
                                        <button onclick="changeQty(${p.id}, 1)" class="w-7 h-7 bg-amber-600 text-white rounded-lg font-black text-sm shadow-xs hover:bg-amber-700 flex items-center justify-center">+</button>
                                    </div>
                                ` : `
                                    <button onclick="addToCart(${p.id})" class="w-full py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold rounded-xl border border-amber-200 transition flex items-center justify-center gap-1">
                                        <span>+ Tambah</span>
                                    </button>
                                `}
                            ` : `
                                <button disabled class="w-full py-2 bg-stone-100 text-stone-400 text-xs font-semibold rounded-xl cursor-not-allowed">
                                    Habis
                                </button>
                            `}
                        </div>
                    </div>
                `;
            }).join('');
        }

        // 3. Filtering & Search
        function setCategory(cat) {
            currentCategory = cat;
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.className = "category-btn px-4 py-2 rounded-xl bg-white border border-stone-200 text-stone-600 hover:bg-stone-50 transition";
            });
            const activeBtn = cat === 'all' ? document.getElementById('btnCatAll') :
                              cat === 'minuman' ? document.getElementById('btnCatMinuman') :
                              document.getElementById('btnCatMakanan');
            activeBtn.className = "category-btn active px-4 py-2 rounded-xl bg-amber-600 text-white shadow-sm transition";
            filterMenu();
        }

        function filterMenu() {
            const query = (document.getElementById('searchMenuInput').value || '').toLowerCase();
            filteredProducts = allProducts.filter(p => {
                const nameMatch = p.name.toLowerCase().includes(query);
                if (!nameMatch) return false;

                if (currentCategory === 'all') return true;
                const isDrink = p.name.toLowerCase().includes('kopi') || p.name.toLowerCase().includes('teh') || p.name.toLowerCase().includes('jeruk') || p.name.toLowerCase().includes('es');
                if (currentCategory === 'minuman') return isDrink;
                if (currentCategory === 'makanan') return !isDrink;
                return true;
            });
            renderMenu(filteredProducts);
        }

        // 4. Cart Operations
        function addToCart(productId) {
            const product = allProducts.find(p => p.id === productId);
            if (!product || !product.available) return;

            if (cart[productId]) {
                cart[productId].quantity += 1;
            } else {
                cart[productId] = { product, quantity: 1 };
            }
            saveCart();
            renderMenu(filteredProducts);
            updateCartUI();
        }

        function changeQty(productId, delta) {
            if (!cart[productId]) return;
            cart[productId].quantity += delta;
            if (cart[productId].quantity <= 0) {
                delete cart[productId];
            }
            saveCart();
            renderMenu(filteredProducts);
            updateCartUI();
            renderCartModalItems();
        }

        function saveCart() {
            sessionStorage.setItem('warkop_cart', JSON.stringify(cart));
        }

        function loadCartFromStorage() {
            const saved = sessionStorage.getItem('warkop_cart');
            if (saved) {
                try { cart = JSON.parse(saved); } catch(e) {}
            }
            updateCartUI();
        }

        function updateCartUI() {
            const items = Object.values(cart);
            const totalQty = items.reduce((sum, item) => sum + item.quantity, 0);
            const totalPrice = items.reduce((sum, item) => sum + (item.quantity * item.product.price), 0);

            // Badges
            const badge = document.getElementById('cartCountBadge');
            if (totalQty > 0) {
                badge.innerText = totalQty;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }

            // Floating bar
            const floatBar = document.getElementById('floatingCartBar');
            if (totalQty > 0) {
                document.getElementById('floatQty').innerText = `${totalQty} item`;
                document.getElementById('floatTotal').innerText = `Rp ${totalPrice.toLocaleString('id-ID')}`;
                floatBar.classList.remove('translate-y-32');
            } else {
                floatBar.classList.add('translate-y-32');
            }

            document.getElementById('cartTotalPrice').innerText = `Rp ${totalPrice.toLocaleString('id-ID')}`;
        }

        // 5. Cart Modal
        function openCartModal() {
            renderCartModalItems();
            document.getElementById('cartModal').classList.remove('hidden');
        }

        function closeCartModal() {
            document.getElementById('cartModal').classList.add('hidden');
        }

        function renderCartModalItems() {
            const container = document.getElementById('cartItemsContainer');
            const items = Object.values(cart);
            const formSection = document.getElementById('checkoutFormSection');

            if (items.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <div class="text-3xl mb-2">🛒</div>
                        <p class="font-bold text-stone-700 text-sm">Keranjang masih kosong</p>
                        <p class="text-xs text-stone-400 mt-1">Pilih minuman dan makanan kesukaanmu dulu ya!</p>
                    </div>
                `;
                formSection.classList.add('hidden');
                return;
            }

            formSection.classList.remove('hidden');
            container.innerHTML = items.map(item => `
                <div class="py-3 flex items-center justify-between gap-3">
                    <div class="flex-1">
                        <h4 class="font-bold text-stone-900 text-sm">${item.product.name}</h4>
                        <p class="text-xs text-stone-500">@ Rp ${item.product.price.toLocaleString('id-ID')}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="changeQty(${item.product.id}, -1)" class="w-7 h-7 rounded-lg bg-stone-100 text-stone-700 font-bold hover:bg-stone-200 flex items-center justify-center">-</button>
                        <span class="text-xs font-black text-stone-900 w-5 text-center">${item.quantity}</span>
                        <button onclick="changeQty(${item.product.id}, 1)" class="w-7 h-7 rounded-lg bg-amber-600 text-white font-bold hover:bg-amber-700 flex items-center justify-center">+</button>
                    </div>
                    <div class="text-right min-w-[70px]">
                        <span class="font-extrabold text-xs text-amber-800">Rp ${(item.quantity * item.product.price).toLocaleString('id-ID')}</span>
                    </div>
                </div>
            `).join('');
        }

        // 6. Submit Order to API
        async function submitOrder() {
            const customerName = document.getElementById('customerNameInput').value.trim();
            if (!customerName) {
                alert('Silakan tulis nama pemesan atau nomor meja terlebih dahulu.');
                document.getElementById('customerNameInput').focus();
                return;
            }

            const itemsArray = Object.values(cart).map(i => ({
                product_id: i.product.id,
                quantity: i.quantity
            }));

            if (itemsArray.length === 0) {
                alert('Keranjang masih kosong.');
                return;
            }

            const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
            const submitBtn = document.getElementById('submitOrderBtn');

            submitBtn.disabled = true;
            submitBtn.innerText = 'Memproses Pesanan...';

            try {
                const res = await fetch('/api/v1/public/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        customer_name: customerName,
                        payment_method: paymentMethod,
                        items: itemsArray
                    })
                });

                const json = await res.json();

                if (res.status === 201 && json.status === 'success') {
                    // Berhasil
                    lastCreatedCode = json.data.order_code;
                    document.getElementById('successOrderCode').innerText = lastCreatedCode;

                    // Kosongkan keranjang
                    cart = {};
                    saveCart();
                    updateCartUI();
                    renderMenu(filteredProducts);
                    closeCartModal();

                    // Tampilkan Success Modal
                    document.getElementById('successModal').classList.remove('hidden');
                } else {
                    alert('Gagal membuat pesanan: ' + (json.message || 'Coba lagi beberapa saat lagi.'));
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Kirim Pesanan Sekarang';
            }
        }

        // 7. Track Order Modal
        function openTrackerModal() {
            document.getElementById('trackerModal').classList.remove('hidden');
        }

        function closeTrackerModal() {
            document.getElementById('trackerModal').classList.add('hidden');
            if (pollingInterval) clearInterval(pollingInterval);
        }

        function closeSuccessModal() {
            document.getElementById('successModal').classList.add('hidden');
        }

        function copyOrderCode() {
            const code = document.getElementById('successOrderCode').innerText;
            navigator.clipboard.writeText(code);
            alert('Kode pesanan ' + code + ' disalin!');
        }

        function viewActiveOrderFromSuccess() {
            closeSuccessModal();
            if (lastCreatedCode) {
                document.getElementById('trackCodeInput').value = lastCreatedCode;
                trackOrderManual();
            }
            openTrackerModal();
        }

        async function trackOrderManual() {
            const code = (document.getElementById('trackCodeInput').value || '').trim();
            if (!code) {
                alert('Masukkan kode pesanan terlebih dahulu.');
                return;
            }

            const box = document.getElementById('trackResultBox');
            box.classList.remove('hidden');
            box.innerHTML = `<p class="text-stone-500 text-center py-4">Mengecek status pesanan ${code}...</p>`;

            await checkOrderStatus(code);

            // Auto poll every 5s if active
            if (pollingInterval) clearInterval(pollingInterval);
            pollingInterval = setInterval(() => {
                checkOrderStatus(code);
            }, 5000);
        }

        async function checkOrderStatus(code) {
            const box = document.getElementById('trackResultBox');
            try {
                const res = await fetch(`/api/v1/public/orders/${encodeURIComponent(code)}`);
                const json = await res.json();

                if (res.status === 200 && json.status === 'success') {
                    const o = json.data;
                    let statusBadge = '';
                    let statusDesc = '';

                    if (o.status === 'pending') {
                        statusBadge = `<span class="px-2.5 py-1 bg-amber-100 text-amber-800 rounded-lg font-bold">🟡 Menunggu Konfirmasi</span>`;
                        statusDesc = 'Pesananmu sudah masuk ke sistem kasir dan sedang menunggu untuk diterima.';
                    } else if (o.status === 'accepted') {
                        statusBadge = `<span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-lg font-bold">🔵 Sedang Disiapkan</span>`;
                        statusDesc = 'Pesanan sudah diterima kasir & sedang dibuatkan barista warkop!';
                    } else if (o.status === 'completed') {
                        statusBadge = `<span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg font-bold">🟢 Selesai</span>`;
                        statusDesc = 'Pesanan sudah selesai! Silakan nikmati atau ambil di kasir.';
                        if (pollingInterval) clearInterval(pollingInterval);
                    } else if (o.status === 'cancelled') {
                        statusBadge = `<span class="px-2.5 py-1 bg-rose-100 text-rose-800 rounded-lg font-bold">🔴 Dibatalkan</span>`;
                        statusDesc = 'Pesanan ini telah dibatalkan.';
                        if (pollingInterval) clearInterval(pollingInterval);
                    }

                    box.innerHTML = `
                        <div class="space-y-3">
                            <div class="flex items-center justify-between border-b border-stone-200 pb-2">
                                <div>
                                    <div class="text-[10px] text-stone-400 uppercase font-black">Kode Pesanan</div>
                                    <div class="font-mono text-sm font-black text-stone-900">${o.order_code}</div>
                                </div>
                                <div>${statusBadge}</div>
                            </div>

                            <p class="text-[11px] text-stone-600">${statusDesc}</p>

                            <div class="bg-white p-2.5 rounded-xl border border-stone-200 space-y-1">
                                <div class="flex justify-between text-[11px]">
                                    <span class="text-stone-500">Pemesan:</span>
                                    <span class="font-bold text-stone-800">${o.customer_name}</span>
                                </div>
                                <div class="flex justify-between text-[11px]">
                                    <span class="text-stone-500">Pembayaran:</span>
                                    <span class="font-bold uppercase text-stone-800">${o.payment_method}</span>
                                </div>
                                <div class="flex justify-between text-[11px] border-t border-stone-100 pt-1">
                                    <span class="text-stone-500">Total:</span>
                                    <span class="font-black text-amber-800">Rp ${o.total_price.toLocaleString('id-ID')}</span>
                                </div>
                            </div>

                            <div>
                                <div class="font-bold text-[11px] text-stone-700 mb-1">Rincian Item:</div>
                                <ul class="divide-y divide-stone-100 text-[11px]">
                                    ${o.items.map(i => `
                                        <li class="py-1 flex justify-between">
                                            <span>${i.name} × ${i.quantity}</span>
                                            <span class="font-semibold text-stone-700">Rp ${i.subtotal.toLocaleString('id-ID')}</span>
                                        </li>
                                    `).join('')}
                                </ul>
                            </div>

                            <div class="text-[10px] text-center text-stone-400 pt-1">
                                ⏱️ Memperbarui status otomatis setiap 5 detik...
                            </div>
                        </div>
                    `;
                } else {
                    box.innerHTML = `
                        <div class="text-center py-4 text-rose-500">
                            <p class="font-bold">Pesanan tidak ditemukan</p>
                            <p class="text-[11px] text-stone-400 mt-1">Periksa kembali kode pesanan yang kamu masukkan.</p>
                        </div>
                    `;
                }
            } catch (err) {
                console.error(err);
            }
        }
    </script>
</body>
</html>
