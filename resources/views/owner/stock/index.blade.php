@extends('layouts.owner', ['title' => 'Stok & Mutasi - Mobeli', 'header' => 'Pengelolaan Stok dan Riwayat Mutasi'])

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-neutral-200 p-5 shadow-xs">
        <h2 class="text-base font-bold font-heading text-neutral-900">Catat Pergerakan Stok</h2>
        <p class="mt-1 text-xs text-neutral-500">Stok masuk menambah persediaan, stok keluar mengurangi persediaan, dan penyesuaian menetapkan jumlah fisik hasil penghitungan.</p>

        <form action="{{ route('admin.stock.store') }}" method="POST" class="mt-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3 items-end">
            @csrf
            <!-- Searchable & Filterable Product Dropdown -->
            <div class="relative" id="productDropdownContainer">
                <label for="product_select_trigger" class="mb-1 block text-xs font-semibold text-neutral-700 dark:text-slate-200">Produk</label>
                
                <!-- Hidden Real Input for Form Submission -->
                <input type="hidden" name="product_id" id="product_id" value="{{ old('product_id') }}" required>

                <!-- Trigger Button -->
                <button type="button" 
                        id="product_select_trigger" 
                        class="w-full rounded-lg border border-neutral-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs flex items-center justify-between text-left transition hover:border-neutral-400 dark:hover:border-slate-600 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer shadow-2xs group"
                        aria-haspopup="listbox" 
                        aria-expanded="false">
                    <span id="product_selected_label" class="truncate font-medium text-neutral-500 dark:text-slate-400">Pilih produk</span>
                    <div class="flex items-center gap-1.5 text-neutral-400 group-hover:text-neutral-600 dark:group-hover:text-slate-300 shrink-0 ml-1.5">
                        <span id="product_clear_btn" class="hidden hover:text-red-500 dark:hover:text-red-400 p-0.5 rounded transition" title="Hapus pilihan">
                            <i data-lucide="x" class="w-3 h-3"></i>
                        </span>
                        <i data-lucide="chevrons-up-down" id="product_chevron_icon" class="w-3.5 h-3.5 transition-transform duration-200"></i>
                    </div>
                </button>

                <!-- Searchable Dropdown Popup Menu -->
                <div id="product_dropdown_menu" 
                     class="hidden absolute left-0 top-full mt-1.5 w-full sm:w-96 max-w-[95vw] z-50 rounded-xl border border-neutral-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-2xl overflow-hidden transition-all duration-150">
                    
                    <!-- Search Input Box -->
                    <div class="p-2 border-b border-neutral-100 dark:border-slate-800 bg-neutral-50/80 dark:bg-slate-850 sticky top-0 z-10">
                        <div class="relative flex items-center">
                            <i data-lucide="search" class="w-3.5 h-3.5 absolute left-2.5 text-neutral-400 dark:text-slate-500 pointer-events-none"></i>
                            <input type="text" 
                                   id="product_search_input" 
                                   autocomplete="off" 
                                   placeholder="Cari nama atau barcode produk..." 
                                   class="w-full pl-8 pr-7 py-1.5 text-xs rounded-lg border border-neutral-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-neutral-900 dark:text-slate-100 placeholder-neutral-400 dark:placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                            <button type="button" id="product_search_clear" class="hidden absolute right-2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 p-0.5">
                                <i data-lucide="x" class="w-3 h-3"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Scrollable Product List -->
                    <ul id="product_options_list" class="max-h-56 overflow-y-auto divide-y divide-neutral-100 dark:divide-slate-800/80 p-1 text-xs focus:outline-none" role="listbox">
                        @foreach($products as $product)
                            <li role="option"
                                data-id="{{ $product->id }}" 
                                data-name="{{ $product->name }}" 
                                data-barcode="{{ $product->barcode ?? '' }}" 
                                data-stock="{{ $product->stock }}"
                                data-unit="{{ $product->unit ?? '' }}"
                                class="product-option-item flex items-center justify-between p-2 rounded-lg cursor-pointer hover:bg-neutral-100/90 dark:hover:bg-slate-800 transition group">
                                <div class="min-w-0 pr-2">
                                    <div class="font-semibold text-neutral-900 dark:text-slate-100 group-hover:text-primary transition truncate product-item-name">
                                        {{ $product->name }}
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-0.5 text-[10.5px] text-neutral-500 dark:text-slate-400">
                                        @if($product->barcode)
                                            <span class="font-mono bg-neutral-100 dark:bg-slate-800 px-1 py-0.2 rounded text-[9.5px] text-neutral-600 dark:text-slate-300">#{{ $product->barcode }}</span>
                                        @endif
                                        @if($product->unit)
                                            <span>Satuan: {{ $product->unit }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="shrink-0 text-right flex items-center gap-1.5">
                                    @if($product->stock > 10)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60 font-mono">
                                            Stok: {{ $product->stock }}
                                        </span>
                                    @elseif($product->stock > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/60 font-mono">
                                            Stok: {{ $product->stock }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 dark:bg-red-950/60 dark:text-red-400 border border-red-200/60 dark:border-red-800/60 font-mono">
                                            Habis (0)
                                        </span>
                                    @endif
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-primary opacity-0 group-[.is-selected]:opacity-100 transition-opacity"></i>
                                </div>
                            </li>
                        @endforeach
                        
                        <!-- Empty State when query has no matches -->
                        <li id="product_no_match" class="hidden py-8 px-4 text-center text-xs text-neutral-500 dark:text-slate-400">
                            <i data-lucide="package-search" class="w-6 h-6 mx-auto mb-1.5 text-neutral-400 dark:text-slate-500 opacity-60"></i>
                            <p class="font-medium">Tidak ada produk yang cocok</p>
                            <p class="text-[10px] text-neutral-400 dark:text-slate-500 mt-0.5">Coba kata kunci lain atau periksa barcode</p>
                        </li>
                    </ul>

                    <!-- Footer Info -->
                    <div class="px-3 py-1.5 bg-neutral-50 dark:bg-slate-850 border-t border-neutral-100 dark:border-slate-800 flex items-center justify-between text-[10px] text-neutral-500 dark:text-slate-400">
                        <span id="product_counter_info">{{ count($products) }} produk tersedia</span>
                        <span class="text-neutral-400 dark:text-slate-500 font-mono">ESC untuk tutup</span>
                    </div>
                </div>
            </div>
            <div>
                <label for="type" class="mb-1 block text-xs font-semibold text-neutral-700">Jenis mutasi</label>
                <select id="type" name="type" required class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-xs">
                    <option value="in" @selected(old('type') === 'in')>Stok masuk</option>
                    <option value="out" @selected(old('type') === 'out')>Stok keluar</option>
                    <option value="adjustment" @selected(old('type') === 'adjustment')>Penyesuaian stok fisik</option>
                </select>
            </div>
            <div>
                <label for="qty" class="mb-1 block text-xs font-semibold text-neutral-700">Jumlah / stok fisik</label>
                <input id="qty" name="qty" type="number" min="0" value="{{ old('qty', 1) }}" required class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-xs">
            </div>
            <div>
                <label for="reason" class="mb-1 block text-xs font-semibold text-neutral-700">Alasan</label>
                <input id="reason" name="reason" value="{{ old('reason') }}" maxlength="255" required placeholder="Contoh: pembelian dari pemasok" class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-xs">
            </div>
            <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white hover:bg-blue-700">Simpan Mutasi</button>
        </form>
        @error('qty') <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
        @error('product_id') <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs">
        <div class="border-b border-neutral-200 bg-neutral-50 p-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-700">Riwayat Mutasi Stok</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-neutral-100 text-neutral-600">
                    <tr>
                        <th class="p-3">Waktu</th>
                        <th class="p-3">Produk</th>
                        <th class="p-3">Jenis</th>
                        <th class="p-3 text-right">Perubahan</th>
                        <th class="p-3 text-center">Sebelum → Sesudah</th>
                        <th class="p-3">Alasan</th>
                        <th class="p-3">Pengguna</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($movements as $movement)
                        <tr>
                            <td class="whitespace-nowrap p-3">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-3 font-semibold text-neutral-900">{{ $movement->product->name ?? 'Produk dihapus' }}</td>
                            <td class="p-3">{{ match($movement->type) { 'in' => 'Masuk', 'out' => 'Keluar', 'return' => 'Retur', default => 'Penyesuaian' } }}</td>
                            <td class="p-3 text-right font-mono">{{ $movement->type === 'adjustment' ? ($movement->qty > 0 ? '+' : '') : ($movement->type === 'out' ? '-' : '+') }}{{ abs($movement->qty) }}</td>
                            <td class="p-3 text-center font-mono">{{ $movement->stock_before }} → {{ $movement->stock_after }}</td>
                            <td class="p-3">{{ $movement->reason ?: '-' }}</td>
                            <td class="p-3">{{ $movement->user->name ?? 'Sistem' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-8 text-center text-neutral-500">Belum ada pergerakan stok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-neutral-100 p-4">{{ $movements->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('productDropdownContainer');
    if (!container) return;

    const triggerBtn = document.getElementById('product_select_trigger');
    const labelSpan = document.getElementById('product_selected_label');
    const chevronIcon = document.getElementById('product_chevron_icon');
    const clearBtn = document.getElementById('product_clear_btn');
    const dropdownMenu = document.getElementById('product_dropdown_menu');
    const searchInput = document.getElementById('product_search_input');
    const searchClearBtn = document.getElementById('product_search_clear');
    const optionsList = document.getElementById('product_options_list');
    const optionItems = Array.from(optionsList.querySelectorAll('.product-option-item'));
    const noMatchEl = document.getElementById('product_no_match');
    const counterInfo = document.getElementById('product_counter_info');
    const hiddenInput = document.getElementById('product_id');
    const totalCount = optionItems.length;

    let highlightedIndex = -1;

    function getVisibleItems() {
        return optionItems.filter(item => !item.classList.contains('hidden'));
    }

    function openDropdown() {
        dropdownMenu.classList.remove('hidden');
        triggerBtn.setAttribute('aria-expanded', 'true');
        chevronIcon.classList.add('rotate-180');
        triggerBtn.classList.remove('border-red-500', 'ring-2', 'ring-red-500/20');
        setTimeout(() => {
            searchInput.focus();
            searchInput.select();
        }, 50);
    }

    function closeDropdown() {
        dropdownMenu.classList.add('hidden');
        triggerBtn.setAttribute('aria-expanded', 'false');
        chevronIcon.classList.remove('rotate-180');
        clearHighlight();
    }

    function clearHighlight() {
        highlightedIndex = -1;
        optionItems.forEach(item => {
            item.classList.remove('bg-neutral-100', 'dark:bg-slate-800', 'ring-1', 'ring-primary');
        });
    }

    function setHighlight(index) {
        const visible = getVisibleItems();
        if (visible.length === 0) return;
        
        clearHighlight();
        if (index < 0) index = visible.length - 1;
        if (index >= visible.length) index = 0;
        
        highlightedIndex = index;
        const target = visible[highlightedIndex];
        target.classList.add('bg-neutral-100', 'dark:bg-slate-800', 'ring-1', 'ring-primary');
        target.scrollIntoView({ block: 'nearest' });
    }

    function selectProduct(id, name, stock) {
        hiddenInput.value = id;
        
        // Update label with product name & stock badge
        labelSpan.innerHTML = `
            <span class="truncate font-semibold text-neutral-900 dark:text-slate-100">${name}</span>
            <span class="ml-1 text-[10px] font-mono px-1.5 py-0.5 rounded bg-neutral-100 dark:bg-slate-800 text-neutral-600 dark:text-slate-300 shrink-0">stok ${stock}</span>
        `;
        labelSpan.classList.remove('text-neutral-500', 'dark:text-slate-400');
        
        clearBtn.classList.remove('hidden');
        
        // Mark selected item in list
        optionItems.forEach(item => {
            const isMatch = item.dataset.id === String(id);
            item.classList.toggle('is-selected', isMatch);
            item.classList.toggle('bg-primary/5', isMatch);
            item.classList.toggle('dark:bg-primary/10', isMatch);
        });

        closeDropdown();
        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function resetSelection() {
        hiddenInput.value = '';
        labelSpan.innerHTML = 'Pilih produk';
        labelSpan.classList.add('text-neutral-500', 'dark:text-slate-400');
        clearBtn.classList.add('hidden');
        optionItems.forEach(item => {
            item.classList.remove('is-selected', 'bg-primary/5', 'dark:bg-primary/10');
        });
        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Toggle dropdown on trigger click
    triggerBtn.addEventListener('click', function (e) {
        if (clearBtn.contains(e.target)) return;
        if (dropdownMenu.classList.contains('hidden')) {
            openDropdown();
        } else {
            closeDropdown();
        }
    });

    // Clear selection button
    clearBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        resetSelection();
    });

    // Option items click handler
    optionItems.forEach(item => {
        item.addEventListener('click', function () {
            selectProduct(this.dataset.id, this.dataset.name, this.dataset.stock);
        });
    });

    // Live search filter
    function filterProducts() {
        const query = searchInput.value.toLowerCase().trim();
        searchClearBtn.classList.toggle('hidden', query.length === 0);

        let matchCount = 0;
        optionItems.forEach(item => {
            const name = (item.dataset.name || '').toLowerCase();
            const barcode = (item.dataset.barcode || '').toLowerCase();
            const isMatch = !query || name.includes(query) || barcode.includes(query);
            item.classList.toggle('hidden', !isMatch);
            if (isMatch) matchCount++;
        });

        noMatchEl.classList.toggle('hidden', matchCount > 0);
        
        if (query) {
            counterInfo.textContent = `${matchCount} dari ${totalCount} produk ditemukan`;
        } else {
            counterInfo.textContent = `${totalCount} produk tersedia`;
        }
        
        clearHighlight();
    }

    searchInput.addEventListener('input', filterProducts);

    searchClearBtn.addEventListener('click', function () {
        searchInput.value = '';
        filterProducts();
        searchInput.focus();
    });

    // Keyboard navigation
    container.addEventListener('keydown', function (e) {
        const isMenuOpen = !dropdownMenu.classList.contains('hidden');

        if (e.key === 'Escape') {
            if (isMenuOpen) {
                e.preventDefault();
                closeDropdown();
                triggerBtn.focus();
            }
            return;
        }

        if (!isMenuOpen) {
            if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openDropdown();
            }
            return;
        }

        const visible = getVisibleItems();

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setHighlight(highlightedIndex + 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setHighlight(highlightedIndex - 1);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (highlightedIndex >= 0 && highlightedIndex < visible.length) {
                const target = visible[highlightedIndex];
                selectProduct(target.dataset.id, target.dataset.name, target.dataset.stock);
            } else if (visible.length === 1) {
                const target = visible[0];
                selectProduct(target.dataset.id, target.dataset.name, target.dataset.stock);
            }
        }
    });

    // Close when clicking outside
    document.addEventListener('click', function (e) {
        if (!container.contains(e.target)) {
            closeDropdown();
        }
    });

    // Pre-select if initial old value exists
    const initialId = hiddenInput.value;
    if (initialId) {
        const initialItem = optionItems.find(item => item.dataset.id === String(initialId));
        if (initialItem) {
            selectProduct(initialItem.dataset.id, initialItem.dataset.name, initialItem.dataset.stock);
        }
    }

    // Form submit validation
    const form = container.closest('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            if (!hiddenInput.value) {
                e.preventDefault();
                triggerBtn.classList.add('border-red-500', 'ring-2', 'ring-red-500/20');
                openDropdown();
            }
        });
    }

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
@endpush
