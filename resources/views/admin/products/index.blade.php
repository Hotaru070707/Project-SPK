@extends('layouts.admin', ['title' => 'Manajemen Produk - Mobeli', 'header' => 'Katalog & Manajemen Stok Produk Sembako'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-neutral-200 shadow-xs">
        <div>
            <h3 class="text-base font-bold font-heading text-neutral-900">Daftar Produk Sembako</h3>
            <p class="text-xs text-neutral-500">Kelola foto produk, harga jual, harga modal (margin), stok, dan barcode kasir.</p>
        </div>
        <button type="button" onclick="openAddModal()" 
                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Produk Baru
        </button>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-100/70 border-b border-neutral-200 text-neutral-600 uppercase font-semibold">
                    <tr>
                        <th class="p-3 w-14 text-center">Foto</th>
                        <th class="p-3">Nama Produk</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3 text-right">Harga Modal</th>
                        <th class="p-3 text-right">Harga Jual</th>
                        <th class="p-3 text-center">Margin</th>
                        <th class="p-3 text-center">Stok Fisik</th>
                        <th class="p-3 text-center">Pengadaan</th>
                        <th class="p-3 text-center font-mono">Barcode</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($products as $p)
                        <tr class="hover:bg-neutral-50 transition">
                            <td class="p-3 text-center">
                                <div class="w-11 h-11 rounded-xl bg-neutral-100 border border-neutral-200/80 overflow-hidden flex items-center justify-center text-lg mx-auto shadow-2xs">
                                    @if($p->image_url)
                                        <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="w-full h-full object-cover">
                                    @else
                                        @if($p->category && $p->category->slug === 'sembako')
                                            🌾
                                        @elseif($p->category && $p->category->slug === 'minuman')
                                            🧃
                                        @elseif($p->category && $p->category->slug === 'bumbu-dapur')
                                            🧂
                                        @elseif($p->category && $p->category->slug === 'snack-camilan')
                                            🍪
                                        @else
                                            📦
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="p-3">
                                <span class="font-bold text-neutral-900 block text-[13px]">{{ $p->name }}</span>
                                <span class="text-[10px] text-neutral-400 font-mono">Satuan: {{ $p->unit }}</span>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded bg-neutral-100 text-[10px] font-medium text-neutral-700">
                                    {{ $p->category->name ?? 'Umum' }}
                                </span>
                            </td>
                            <td class="p-3 text-right price-font text-neutral-500">
                                Rp {{ number_format($p->cost_price, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-right price-font font-bold text-neutral-900">
                                Rp {{ number_format($p->sell_price, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center font-bold text-emerald-700">
                                {{ $p->margin }}%
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $p->stock <= $p->min_stock ? 'bg-orange-100 text-orange-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $p->stock }} {{ $p->unit }}
                                </span>
                            </td>
                            <td class="p-3 text-center font-mono text-neutral-600">
                                {{ $p->supplier_lead_time_days }} hari
                            </td>
                            <td class="p-3 text-center font-mono text-[11px] text-neutral-600">
                                {{ $p->barcode ?? '-' }}
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Tombol Edit Produk -->
                                    <button type="button" 
                                            onclick='openEditModal(@json($p))'
                                            class="p-1.5 text-neutral-600 hover:text-emerald-700 rounded-lg hover:bg-emerald-50 transition" 
                                            title="Edit Produk">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>

                                    <!-- Tombol Hapus Produk -->
                                    <form action="{{ route('admin.products.delete', $p->id) }}" method="POST" onsubmit="return confirm('Hapus produk {{ addslashes($p->name) }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-neutral-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition" title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="p-8 text-center text-neutral-400">
                                Belum ada produk. Klik tombol "Tambah Produk Baru" untuk menambahkan produk pertama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-neutral-100">
            {{ $products->links() }}
        </div>
    </div>
</div>

<!-- Modal Tambah Produk -->
<div id="modalAddProduct" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-neutral-100">
            <div>
                <h3 class="font-bold text-base text-neutral-900">Tambah Produk Sembako Baru</h3>
                <p class="text-xs text-neutral-500">Isi data lengkap beserta foto produk untuk katalog toko.</p>
            </div>
            <button type="button" onclick="closeAddModal()" class="text-neutral-400 hover:text-neutral-600 text-lg leading-none">✕</button>
        </div>

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5 text-xs">
            @csrf

            <!-- Upload Foto Produk Baru -->
            <div>
                <label class="block font-bold text-neutral-700 mb-1">Foto Produk (Opsional)</label>
                <div class="flex items-center gap-3 p-3 bg-neutral-50 rounded-xl border border-neutral-200">
                    <div id="addPhotoPreviewBox" class="w-16 h-16 rounded-xl bg-white border border-dashed border-neutral-300 flex items-center justify-center overflow-hidden shrink-0 shadow-xs">
                        <i data-lucide="image" class="w-6 h-6 text-neutral-400" id="add_empty_icon"></i>
                        <img id="add_image_preview" src="" alt="Preview" class="w-full h-full object-cover hidden">
                    </div>
                    <div class="flex-1 space-y-1">
                        <input type="file" name="image" id="add_image_input" accept="image/*" onchange="previewAddImage(this)"
                               class="w-full text-xs text-neutral-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                        <p class="text-[10px] text-neutral-400">Format: JPG, PNG, WEBP. Maks 5MB. Foto akan tampil di halaman belanja pelanggan.</p>
                    </div>
                </div>
            </div>

            <div>
                <label class="block font-bold text-neutral-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Beras Pandan Wangi 5kg"
                       class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs text-neutral-900 focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="category_id" required class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs focus:ring-2 focus:ring-emerald-500">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Barcode POS (Scan)</label>
                    <input type="text" name="barcode" placeholder="899..."
                           class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs font-mono focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-neutral-700">Harga Modal (Rp) <span class="text-red-500">*</span></label>
                        <span class="text-[10px] text-neutral-400 font-mono">±1.000 / klik</span>
                    </div>
                    <input type="number" name="cost_price" required placeholder="10000" min="0" step="1000"
                           class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs font-mono focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-neutral-700">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                        <span class="text-[10px] text-emerald-600 font-mono">±1.000 / klik</span>
                    </div>
                    <input type="number" name="sell_price" required placeholder="12500" min="0" step="1000"
                           class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs font-mono font-bold text-emerald-800 focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Stok Awal <span class="text-red-500">*</span></label>
                    <input type="number" name="stock" value="20" required min="0"
                           class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs font-mono focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Stok Min <span class="text-red-500">*</span></label>
                    <input type="number" name="min_stock" value="5" required min="0"
                           class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs font-mono focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Lead Time (hari) <span class="text-red-500">*</span></label>
                    <input type="number" name="supplier_lead_time_days" value="3" required min="0" max="365"
                           class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs font-mono focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Satuan <span class="text-red-500">*</span></label>
                    <input type="text" name="unit" value="pcs" required
                           class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="block font-bold text-neutral-700 mb-1">Deskripsi Singkat</label>
                <textarea name="description" rows="2" placeholder="Keterangan produk sembako, kualitas, expired..."
                          class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2 text-xs focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <div class="pt-3 border-t border-neutral-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 bg-neutral-100 text-neutral-700 rounded-lg font-bold">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold shadow-sm flex items-center gap-1.5">
                    <i data-lucide="plus" class="w-4 h-4"></i> Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Produk -->
<div id="modalEditProduct" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-neutral-100">
            <div>
                <h3 class="font-bold text-base text-neutral-900">Edit Data Produk</h3>
                <p class="text-xs text-neutral-500">Perbarui informasi harga, stok, atau foto produk.</p>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-neutral-400 hover:text-neutral-600 text-lg leading-none">✕</button>
        </div>

        <form id="formEditProduct" action="" method="POST" enctype="multipart/form-data" class="space-y-3.5 text-xs">
            @csrf
            @method('PUT')

            <!-- Upload / Ganti Foto Produk -->
            <div>
                <label class="block font-bold text-neutral-700 mb-1">Foto Produk</label>
                <div class="flex items-center gap-3 p-3 bg-neutral-50 rounded-xl border border-neutral-200">
                    <div id="editPhotoPreviewBox" class="w-16 h-16 rounded-xl bg-white border border-neutral-200 flex items-center justify-center overflow-hidden shrink-0 shadow-xs">
                        <i data-lucide="image" class="w-6 h-6 text-neutral-400" id="edit_empty_icon"></i>
                        <img id="edit_image_preview" src="" alt="Preview" class="w-full h-full object-cover hidden">
                    </div>
                    <div class="flex-1 space-y-1.5">
                        <input type="file" name="image" id="edit_image_input" accept="image/*" onchange="previewEditImage(this)"
                               class="w-full text-xs text-neutral-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                        <p class="text-[10px] text-neutral-400">Pilih file baru untuk mengganti foto produk saat ini.</p>
                        <div id="edit_has_image_option" class="hidden flex items-center gap-1.5 pt-1 border-t border-neutral-200/80">
                            <input type="checkbox" name="remove_image" id="edit_remove_image" value="1" class="rounded text-red-600 focus:ring-red-500">
                            <label for="edit_remove_image" class="text-[11px] text-red-600 font-semibold cursor-pointer">Hapus foto ini (gunakan icon kategori default)</label>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block font-bold text-neutral-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="edit_name" required
                       class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs text-neutral-900 focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="category_id" id="edit_category_id" required class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs focus:ring-2 focus:ring-emerald-500">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Barcode POS (Scan)</label>
                    <input type="text" name="barcode" id="edit_barcode" placeholder="899..."
                           class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs font-mono focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-neutral-700">Harga Modal (Rp) <span class="text-red-500">*</span></label>
                        <span class="text-[10px] text-neutral-400 font-mono">±1.000 / klik</span>
                    </div>
                    <input type="number" name="cost_price" id="edit_cost_price" required min="0" step="1000"
                           class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs font-mono focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-neutral-700">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                        <span class="text-[10px] text-emerald-600 font-mono">±1.000 / klik</span>
                    </div>
                    <input type="number" name="sell_price" id="edit_sell_price" required min="0" step="1000"
                           class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs font-mono font-bold text-emerald-800 focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Stok Fisik <span class="text-red-500">*</span></label>
                    <input type="number" name="stock" id="edit_stock" required min="0"
                           class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs font-mono focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Stok Min <span class="text-red-500">*</span></label>
                    <input type="number" name="min_stock" id="edit_min_stock" required min="0"
                           class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs font-mono focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Lead Time (hari) <span class="text-red-500">*</span></label>
                    <input type="number" name="supplier_lead_time_days" id="edit_supplier_lead_time_days" required min="0" max="365"
                           class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs font-mono focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Satuan <span class="text-red-500">*</span></label>
                    <input type="text" name="unit" id="edit_unit" required
                           class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="block font-bold text-neutral-700 mb-1">Deskripsi Singkat</label>
                <textarea name="description" id="edit_description" rows="2"
                          class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2 text-xs focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <div>
                <label for="edit_stock_change_reason" class="block font-bold text-neutral-700 mb-1">Alasan perubahan stok <span class="text-neutral-400 font-normal">(wajib jika stok berubah)</span></label>
                <input type="text" name="stock_change_reason" id="edit_stock_change_reason" maxlength="255"
                       class="w-full bg-neutral-50 border border-neutral-300 rounded-lg p-2.5 text-xs focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="pt-3 border-t border-neutral-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-neutral-100 text-neutral-700 rounded-lg font-bold">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold shadow-sm flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('modalAddProduct').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closeAddModal() {
        document.getElementById('modalAddProduct').classList.add('hidden');
    }

    function previewAddImage(input) {
        const previewImg = document.getElementById('add_image_preview');
        const emptyIcon = document.getElementById('add_empty_icon');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
                if (emptyIcon) emptyIcon.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            previewImg.src = '';
            previewImg.classList.add('hidden');
            if (emptyIcon) emptyIcon.classList.remove('hidden');
        }
    }

    function openEditModal(product) {
        const form = document.getElementById('formEditProduct');
        form.action = `/admin/produk/${product.id}`;

        document.getElementById('edit_name').value = product.name || '';
        document.getElementById('edit_category_id').value = product.category_id || '';
        document.getElementById('edit_barcode').value = product.barcode || '';
        document.getElementById('edit_cost_price').value = Math.round(product.cost_price) || 0;
        document.getElementById('edit_sell_price').value = Math.round(product.sell_price) || 0;
        document.getElementById('edit_stock').value = product.stock || 0;
        document.getElementById('edit_min_stock').value = product.min_stock || 0;
        document.getElementById('edit_supplier_lead_time_days').value = product.supplier_lead_time_days ?? '';
        document.getElementById('edit_stock_change_reason').value = '';
        document.getElementById('edit_unit').value = product.unit || 'pcs';
        document.getElementById('edit_description').value = product.description || '';

        // Image preview
        const previewImg = document.getElementById('edit_image_preview');
        const emptyIcon = document.getElementById('edit_empty_icon');
        const hasImageOption = document.getElementById('edit_has_image_option');
        const removeCheckbox = document.getElementById('edit_remove_image');
        const fileInput = document.getElementById('edit_image_input');
        
        fileInput.value = '';
        if (removeCheckbox) removeCheckbox.checked = false;

        if (product.image_url) {
            previewImg.src = product.image_url;
            previewImg.classList.remove('hidden');
            if (emptyIcon) emptyIcon.classList.add('hidden');
            if (hasImageOption) hasImageOption.classList.remove('hidden');
        } else {
            previewImg.src = '';
            previewImg.classList.add('hidden');
            if (emptyIcon) emptyIcon.classList.remove('hidden');
            if (hasImageOption) hasImageOption.classList.add('hidden');
        }

        document.getElementById('modalEditProduct').classList.remove('hidden');
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function closeEditModal() {
        document.getElementById('modalEditProduct').classList.add('hidden');
    }

    function previewEditImage(input) {
        const previewImg = document.getElementById('edit_image_preview');
        const emptyIcon = document.getElementById('edit_empty_icon');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
                if (emptyIcon) emptyIcon.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Allow custom typed values (like 12500, 7500) without browser stepMismatch blocking
    document.querySelectorAll('input[type="number"][step="1000"]').forEach(input => {
        input.addEventListener('invalid', function(e) {
            if (this.validity.stepMismatch) {
                this.step = 'any';
            }
        });
        input.addEventListener('focus', function() {
            this.step = '1000';
        });
        input.addEventListener('mouseenter', function() {
            this.step = '1000';
        });
    });
</script>
@endsection
