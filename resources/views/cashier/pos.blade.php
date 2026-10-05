@extends('layouts.cashier', ['title' => 'Mesin POS Kasir - Mobeli'])

@section('content')
<div class="flex-1 flex flex-col md:flex-row overflow-hidden h-[calc(100vh-50px)]">
    <!-- Left Column: Product Selection & Barcode Search -->
    <div class="flex-1 flex flex-col bg-slate-50 border-r border-slate-200 overflow-hidden">
        <!-- Search & Filter Bar -->
        <div class="p-3 bg-white border-b border-slate-200 flex items-center gap-3">
            <div class="relative flex-1">
                <input type="text" id="posSearch" placeholder="Ketik nama produk atau SCAN BARCODE..." autofocus
                       oninput="filterProducts()" 
                       class="w-full bg-slate-100 border border-slate-300 rounded-lg pl-9 pr-3 py-2 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                <i data-lucide="scan-barcode" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
            </div>
            <button type="button" onclick="document.getElementById('posSearch').value=''; filterProducts();" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg">
                Reset
            </button>
        </div>

        <!-- Product Grid -->
        <div class="flex-1 overflow-y-auto p-3 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5" id="productGrid">
            @foreach($products as $p)
                <button type="button" onclick="addToPosCart({{ json_encode($p) }})"
                        data-name="{{ strtolower($p->name) }}" data-barcode="{{ $p->barcode }}"
                        class="product-item bg-white p-3 rounded-xl border border-slate-200 hover:border-emerald-500 hover:shadow-md transition text-left flex flex-col justify-between group active:scale-[0.98]">
                    <div>
                        <div class="flex items-center justify-between text-[10px] text-slate-400 font-mono mb-1">
                            <span>{{ $p->barcode ?? 'SKU' }}</span>
                            <span class="font-bold {{ $p->stock <= $p->min_stock ? 'text-red-500' : 'text-slate-600' }}">
                                Stok: {{ $p->stock }}
                            </span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-900 line-clamp-2 group-hover:text-emerald-700 leading-snug">
                            {{ $p->name }}
                        </h4>
                    </div>
                    <div class="mt-2 pt-2 border-t border-slate-100 flex items-baseline justify-between">
                        <span class="text-xs font-extrabold text-emerald-700 price-font">
                            Rp {{ number_format($p->sell_price, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] text-slate-400">/ {{ $p->unit }}</span>
                    </div>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Right Column: Current Transaction Cart & Payment -->
    <div class="w-full md:w-[420px] bg-white flex flex-col border-l border-slate-200 shadow-lg">
        <!-- Header Cart Info -->
        <div class="p-3 bg-slate-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="shopping-cart" class="w-4 h-4 text-emerald-400"></i>
                <span class="text-xs font-bold uppercase tracking-wider">Antrian Kasir</span>
            </div>
            <button type="button" onclick="clearPosCart()" class="text-[11px] text-red-300 hover:text-red-100 underline font-medium">
                Kosongkan
            </button>
        </div>

        <!-- Member Selector for Auto Discount -->
        <div class="p-2.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2 text-xs">
            <span class="text-slate-500 font-medium text-[11px]">Member:</span>
            <select id="memberSelect" onchange="calculatePosTotal()" class="flex-1 bg-white border border-slate-300 rounded px-2 py-1 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="0" data-tier="none">Pelanggan Umum (Tanpa Diskon)</option>
                @foreach($members as $m)
                    <option value="{{ $m->id }}" data-tier="{{ $m->member_tier }}">
                        {{ $m->name }} ({{ strtoupper($m->member_tier) }} - {{ $m->member_tier === 'gold' ? '5%' : ($m->member_tier === 'silver' ? '3%' : '0%') }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Cart Items List (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-3 divide-y divide-slate-100 space-y-2" id="posCartList">
            <!-- Dynamic Items Rendered by JS -->
            <div class="py-12 text-center text-slate-400 text-xs">
                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-1 text-slate-300"></i>
                Belum ada produk dipilih.<br>Klik produk di sebelah kiri atau scan barcode.
            </div>
        </div>

        <!-- Bottom Payment Calculation & CTA -->
        <div class="p-4 bg-slate-50 border-t border-slate-200 space-y-2 text-xs">
            <div class="flex justify-between text-slate-600">
                <span>Subtotal Barang:</span>
                <span class="font-bold text-slate-900 price-font" id="posSubtotal">Rp 0</span>
            </div>
            <div class="flex justify-between text-emerald-700 font-semibold" id="posDiscountRow">
                <span>Diskon Member:</span>
                <span class="price-font" id="posDiscount">- Rp 0</span>
            </div>
            <div class="pt-2 border-t border-slate-200 flex justify-between items-baseline">
                <span class="text-sm font-black text-slate-900">TOTAL BAYAR:</span>
                <span class="text-2xl font-black text-emerald-700 price-font" id="posTotal">Rp 0</span>
            </div>

            <!-- Big Pay Button -->
            <button type="button" onclick="openPaymentModal()" id="btnPay" disabled
                    class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-extrabold text-sm rounded-xl shadow-md transition flex items-center justify-center gap-2 mt-2">
                <i data-lucide="banknote" class="w-5 h-5"></i> BAYAR SEKARANG (F9)
            </button>
        </div>
    </div>
</div>

<!-- Modal Pembayaran & Kembalian -->
<div id="paymentModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-extrabold text-base text-slate-900">Pembayaran Kasir POS</h3>
            <button type="button" onclick="closePaymentModal()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <div>
            <span class="text-xs text-slate-500">Total Tagihan Transaksi</span>
            <div class="text-2xl font-black text-emerald-700 price-font" id="modalTotalPay">Rp 0</div>
        </div>

        <!-- Pilihan Metode Bayar -->
        <div class="grid grid-cols-3 gap-2">
            <button type="button" onclick="setPaymentMethod('cash')" id="btnMethodCash" class="p-2.5 rounded-xl border-2 border-emerald-600 bg-emerald-50 text-emerald-900 font-bold text-xs text-center">
                💵 TUNAI
            </button>
            <button type="button" onclick="setPaymentMethod('midtrans_qris')" id="btnMethodQris" class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-bold text-xs text-center">
                📱 QRIS
            </button>
            <button type="button" onclick="setPaymentMethod('manual_transfer')" id="btnMethodTf" class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-bold text-xs text-center">
                🏦 TRANSFER
            </button>
        </div>

        <!-- Input Uang Diterima (Khusus Tunai) -->
        <div id="cashInputSection" class="space-y-3">
            <label class="block text-xs font-bold text-slate-700">Uang Tunai Diterima:</label>
            <input type="number" id="cashGiven" placeholder="0" oninput="calculateChange()"
                   class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-lg font-bold text-slate-900 font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">

            <!-- Tombol Pecahan Cepat -->
            <div class="grid grid-cols-4 gap-1.5 text-xs font-mono font-bold">
                <button type="button" onclick="setCashFast('exact')" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-lg">Uang Pas</button>
                <button type="button" onclick="setCashFast(50000)" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-lg">50.000</button>
                <button type="button" onclick="setCashFast(100000)" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-lg">100.000</button>
                <button type="button" onclick="setCashFast(200000)" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-lg">200.000</button>
            </div>

            <!-- Kembalian Display -->
            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 flex justify-between items-baseline">
                <span class="text-xs font-bold text-emerald-900">Uang Kembalian:</span>
                <span class="text-xl font-black text-emerald-700 price-font" id="modalChange">Rp 0</span>
            </div>
        </div>

        <button type="button" onclick="submitPosTransaction()" id="btnSubmitTx"
                class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm rounded-xl shadow-md transition flex items-center justify-center gap-2">
            <i data-lucide="printer" class="w-4 h-4"></i> SELESAIKAN & CETAK STRUK
        </button>
    </div>
</div>

<!-- Modal Struk Thermal Preview -->
<div id="receiptModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl font-mono text-xs text-slate-800 space-y-3">
        <div class="text-center pb-2 border-b border-dashed border-slate-300">
            <h2 class="font-extrabold text-sm text-slate-900">MOBELI</h2>
            <p class="text-[10px] text-slate-500">Jl. Merdeka No. 45, Sukajadi</p>
            <p class="text-[10px] text-slate-500">Telp: 0812-3456-7890</p>
        </div>

        <div class="text-[10px] space-y-0.5 border-b border-dashed border-slate-300 pb-2">
            <div class="flex justify-between"><span id="rcInvoice">INV: POS-...</span><span id="rcDate">...</span></div>
            <div class="flex justify-between"><span>Kasir: {{ auth()->user()->name }}</span><span id="rcMember">Umum</span></div>
        </div>

        <div id="rcItems" class="space-y-1 py-1 max-h-48 overflow-y-auto border-b border-dashed border-slate-300">
            <!-- items -->
        </div>

        <div class="space-y-1 text-[11px] pt-1 border-b border-dashed border-slate-300 pb-2">
            <div class="flex justify-between"><span>Subtotal:</span><span id="rcSubtotal">Rp 0</span></div>
            <div class="flex justify-between"><span>Diskon:</span><span id="rcDiscount">Rp 0</span></div>
            <div class="flex justify-between font-bold text-slate-900"><span>TOTAL:</span><span id="rcTotal">Rp 0</span></div>
            <div class="flex justify-between"><span>Bayar (<span id="rcMethod">CASH</span>):</span><span id="rcPay">Rp 0</span></div>
            <div class="flex justify-between font-bold"><span>Kembalian:</span><span id="rcChange">Rp 0</span></div>
        </div>

        <div class="text-center text-[10px] text-slate-500 pt-2">
            <p>Terima kasih telah berbelanja di Mobeli!</p>
            <p>Barang yang sudah dibeli tidak dapat ditukar.</p>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <button type="button" onclick="window.print()" class="flex-1 py-2 bg-emerald-600 text-white font-bold rounded-lg text-xs">
                Cetak (Print)
            </button>
            <button type="button" onclick="closeReceiptModal()" class="px-4 py-2 bg-slate-200 text-slate-800 font-bold rounded-lg text-xs">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    let posCart = [];
    let currentMethod = 'cash';
    let currentTotal = 0;

    function addToPosCart(product) {
        let existing = posCart.find(i => i.id === product.id);
        if (existing) {
            existing.qty += 1;
        } else {
            posCart.push({
                id: product.id,
                name: product.name,
                price: parseFloat(product.sell_price),
                cost_price: parseFloat(product.cost_price),
                unit: product.unit,
                qty: 1,
            });
        }
        renderPosCart();
    }

    function changeQty(id, delta) {
        let item = posCart.find(i => i.id === id);
        if (item) {
            item.qty += delta;
            if (item.qty <= 0) {
                posCart = posCart.filter(i => i.id !== id);
            }
        }
        renderPosCart();
    }

    function clearPosCart() {
        if (confirm('Yakin kosongkan antrian kasir?')) {
            posCart = [];
            renderPosCart();
        }
    }

    function renderPosCart() {
        const container = document.getElementById('posCartList');
        if (posCart.length === 0) {
            container.innerHTML = `
                <div class="py-12 text-center text-slate-400 text-xs">
                    <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-1 text-slate-300"></i>
                    Belum ada produk dipilih.<br>Klik produk di sebelah kiri atau scan barcode.
                </div>`;
            document.getElementById('btnPay').disabled = true;
            document.getElementById('posSubtotal').innerText = 'Rp 0';
            document.getElementById('posDiscount').innerText = '- Rp 0';
            document.getElementById('posTotal').innerText = 'Rp 0';
            lucide.createIcons();
            return;
        }

        let html = '';
        posCart.forEach(item => {
            html += `
                <div class="flex items-center justify-between py-1.5">
                    <div class="max-w-[180px]">
                        <h5 class="font-bold text-slate-900 text-xs truncate">${item.name}</h5>
                        <span class="text-[10px] text-slate-400 font-mono">Rp ${item.price.toLocaleString('id-ID')}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center border border-slate-200 rounded overflow-hidden">
                            <button type="button" onclick="changeQty(${item.id}, -1)" class="px-2 py-0.5 hover:bg-slate-100 font-bold">-</button>
                            <span class="px-2 text-xs font-bold font-mono">${item.qty}</span>
                            <button type="button" onclick="changeQty(${item.id}, 1)" class="px-2 py-0.5 hover:bg-slate-100 font-bold">+</button>
                        </div>
                        <span class="font-bold text-slate-900 text-xs price-font w-20 text-right">
                            Rp ${(item.price * item.qty).toLocaleString('id-ID')}
                        </span>
                    </div>
                </div>`;
        });

        container.innerHTML = html;
        document.getElementById('btnPay').disabled = false;
        calculatePosTotal();
        lucide.createIcons();
    }

    function calculatePosTotal() {
        let subtotal = posCart.reduce((sum, i) => sum + (i.price * i.qty), 0);
        let memberSelect = document.getElementById('memberSelect');
        let tier = memberSelect.options[memberSelect.selectedIndex].getAttribute('data-tier') || 'none';
        
        let discount = 0;
        if (tier === 'gold') discount = subtotal * 0.05;
        else if (tier === 'silver') discount = subtotal * 0.03;

        currentTotal = Math.max(0, subtotal - discount);

        document.getElementById('posSubtotal').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        document.getElementById('posDiscount').innerText = '- Rp ' + discount.toLocaleString('id-ID');
        document.getElementById('posTotal').innerText = 'Rp ' + currentTotal.toLocaleString('id-ID');
        document.getElementById('modalTotalPay').innerText = 'Rp ' + currentTotal.toLocaleString('id-ID');
    }

    function openPaymentModal() {
        if (posCart.length === 0) return;
        document.getElementById('paymentModal').classList.remove('hidden');
        setPaymentMethod('cash');
        setCashFast('exact');
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }

    function setPaymentMethod(method) {
        currentMethod = method;
        ['btnMethodCash', 'btnMethodQris', 'btnMethodTf'].forEach(id => {
            document.getElementById(id).classList.remove('border-emerald-600', 'bg-emerald-50', 'text-emerald-900');
            document.getElementById(id).classList.add('border-slate-200', 'bg-slate-50', 'text-slate-700');
        });

        if (method === 'cash') {
            document.getElementById('btnMethodCash').classList.add('border-emerald-600', 'bg-emerald-50', 'text-emerald-900');
            document.getElementById('cashInputSection').classList.remove('hidden');
        } else {
            document.getElementById(method === 'midtrans_qris' ? 'btnMethodQris' : 'btnMethodTf').classList.add('border-emerald-600', 'bg-emerald-50', 'text-emerald-900');
            document.getElementById('cashInputSection').classList.add('hidden');
        }
        calculateChange();
    }

    function setCashFast(amount) {
        if (amount === 'exact') {
            document.getElementById('cashGiven').value = currentTotal;
        } else {
            document.getElementById('cashGiven').value = amount;
        }
        calculateChange();
    }

    function calculateChange() {
        let cash = parseFloat(document.getElementById('cashGiven').value) || 0;
        let change = Math.max(0, cash - currentTotal);
        document.getElementById('modalChange').innerText = 'Rp ' + change.toLocaleString('id-ID');
    }

    function filterProducts() {
        let query = document.getElementById('posSearch').value.toLowerCase().trim();
        let items = document.querySelectorAll('.product-item');
        items.forEach(el => {
            let name = el.getAttribute('data-name');
            let barcode = el.getAttribute('data-barcode');
            if (name.includes(query) || (barcode && barcode.includes(query))) {
                el.style.display = 'flex';
            } else {
                el.style.display = 'none';
            }
        });
    }

    async function submitPosTransaction() {
        let memberSelect = document.getElementById('memberSelect');
        let customerId = memberSelect.value !== "0" ? memberSelect.value : null;
        let subtotal = posCart.reduce((sum, i) => sum + (i.price * i.qty), 0);
        let cashGiven = parseFloat(document.getElementById('cashGiven').value) || currentTotal;

        if (currentMethod === 'cash' && cashGiven < currentTotal) {
            alert('Nominal uang tunai kurang dari total belanjaan!');
            return;
        }

        let payload = {
            items: posCart,
            payment_method: currentMethod,
            customer_id: customerId,
            subtotal: subtotal,
            discount: subtotal - currentTotal,
            total: currentTotal,
            cash_given: cashGiven,
        };

        try {
            let res = await fetch("{{ route('cashier.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(payload)
            });

            let data = await res.json();
            if (data.success) {
                closePaymentModal();
                showReceipt(data, payload);
                posCart = [];
                renderPosCart();
            } else {
                alert('Gagal: ' + data.message);
            }
        } catch (err) {
            alert('Terjadi kesalahan koneksi server.');
        }
    }

    function showReceipt(data, req) {
        document.getElementById('rcInvoice').innerText = data.order_number;
        document.getElementById('rcDate').innerText = new Date().toLocaleTimeString('id-ID');
        document.getElementById('rcSubtotal').innerText = 'Rp ' + req.subtotal.toLocaleString('id-ID');
        document.getElementById('rcDiscount').innerText = '- Rp ' + req.discount.toLocaleString('id-ID');
        document.getElementById('rcTotal').innerText = 'Rp ' + req.total.toLocaleString('id-ID');
        document.getElementById('rcMethod').innerText = req.payment_method.toUpperCase();
        document.getElementById('rcPay').innerText = 'Rp ' + (req.cash_given || req.total).toLocaleString('id-ID');
        document.getElementById('rcChange').innerText = 'Rp ' + (data.change || 0).toLocaleString('id-ID');

        let itemsHtml = '';
        req.items.forEach(i => {
            itemsHtml += `
                <div class="flex justify-between">
                    <span>${i.name} (${i.qty}×)</span>
                    <span>Rp ${(i.price * i.qty).toLocaleString('id-ID')}</span>
                </div>`;
        });
        document.getElementById('rcItems').innerHTML = itemsHtml;
        document.getElementById('receiptModal').classList.remove('hidden');
    }

    function closeReceiptModal() {
        document.getElementById('receiptModal').classList.add('hidden');
    }
</script>
@endsection
