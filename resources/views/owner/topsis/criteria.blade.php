@extends('layouts.owner', ['title' => 'Kriteria TOPSIS - Mobeli', 'header' => 'Pengaturan Kriteria & Bobot TOPSIS'])

@section('content')
<div class="space-y-6 pb-10">
    <section class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-xs">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="max-w-3xl">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-700">Sistem Pendukung Keputusan</span>
                <h1 class="mt-1 font-heading text-xl font-extrabold text-neutral-900">Pengaturan Kriteria &amp; Bobot TOPSIS</h1>
                <p class="mt-2 text-sm leading-6 text-neutral-600">
                    Atur bobot penilaian dan gunakan data penjualan, stok, serta waktu pengadaan untuk menentukan prioritas restock barang.
                </p>
            </div>
            <a href="{{ route('admin.topsis.history') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-neutral-200 px-4 py-2.5 text-xs font-bold text-neutral-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-800">
                <i data-lucide="history" class="h-4 w-4"></i>
                Riwayat Perhitungan
            </a>
        </div>
    </section>

    @if (session('info'))
        <div role="status" class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">{{ session('info') }}</div>
    @endif
    @if (session('success'))
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-bold">Periksa kembali bobot kriteria.</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach ($criteria as $criterion)
            @php
                $summaryIcon = match ($criterion->code) {
                    'C1' => 'shopping-cart',
                    'C2' => 'boxes',
                    'C3' => 'truck',
                    default => 'chart-no-axes-column',
                };
            @endphp
            <article class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-xs">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                        <i data-lucide="{{ $summaryIcon }}" class="h-5 w-5"></i>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $criterion->type === 'benefit' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $criterion->type === 'benefit' ? 'Benefit' : 'Cost' }}
                    </span>
                </div>
                <div class="mt-4 flex items-end justify-between gap-2">
                    <div>
                        <p class="text-sm font-bold text-neutral-900">{{ $criterion->name }}</p>
                        <p class="mt-1 text-xs text-neutral-500">{{ $criterion->code }} · {{ $criterion->name }}</p>
                    </div>
                    <p data-weight-display="{{ $criterion->code }}" class="font-mono text-2xl font-extrabold text-blue-700">{{ number_format((float) $criterion->weight * 100, 2, ',', '.') }}%</p>
                </div>
            </article>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs">
        <div class="flex flex-col gap-3 border-b border-neutral-100 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-heading text-base font-bold text-neutral-900">Kriteria dan sumber data</h2>
                <p class="mt-1 text-xs text-neutral-500">Nilai bobot harus berjumlah tepat 100% sebelum perhitungan dapat dijalankan.</p>
            </div>
            <div class="flex items-center gap-2 text-xs" aria-live="polite">
                <span class="font-semibold text-neutral-600">Total bobot</span>
                <span id="topsis-weight-total" class="rounded-full px-3 py-1.5 font-mono font-extrabold {{ $criteriaValid ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                    {{ number_format((float) $totalWeight * 100, 2, ',', '.') }}%
                </span>
                <span id="topsis-weight-status" class="font-bold {{ $criteriaValid ? 'text-emerald-700' : 'text-red-700' }}">{{ $criteriaValid ? 'Valid' : 'Tidak Valid' }}</span>
            </div>
        </div>
        <p id="topsis-weight-error" class="hidden border-b border-neutral-100 bg-red-50 px-5 py-3 text-xs font-semibold text-red-800" role="alert" aria-live="polite"></p>

        <form id="topsis-criteria-form" data-topsis-weights data-base-valid="{{ $criteriaValid && $dataComplete ? 'true' : 'false' }}" data-editing="{{ old('weights') !== null ? 'true' : 'false' }}" action="{{ route('admin.topsis.criteria.update') }}" method="POST">
            @csrf
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-xs">
                    <thead class="border-b border-neutral-200 bg-neutral-50 text-[10px] uppercase tracking-wide text-neutral-500">
                        <tr>
                            <th class="p-4">Kode</th>
                            <th class="p-4">Nama kriteria</th>
                            <th class="p-4">Penjelasan bisnis</th>
                            <th class="p-4">Tipe</th>
                            <th class="p-4">Sumber data</th>
                            <th class="w-36 p-4">Bobot</th>
                            <th class="p-4">Kelengkapan data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @foreach ($criteria as $criterion)
                            @php
                                $isComplete = match ($criterion->code) {
                                    'C1' => $missingAlternatives === [],
                                    'C2' => $productsWithInvalidStock->isEmpty() && $missingAlternatives === [],
                                    'C3' => $productsMissingLeadTime->isEmpty() && $missingAlternatives === [],
                                    default => false,
                                };
                                $businessDescription = match ($criterion->code) {
                                    'C1' => 'Mengukur jumlah unit yang terjual selama periode penilaian.',
                                    'C2' => 'Mengukur stok barang yang tersedia saat perhitungan dilakukan.',
                                    'C3' => 'Mengukur waktu yang dibutuhkan supplier untuk menyediakan barang.',
                                    default => $criterion->description,
                                };
                            @endphp
                            <tr class="align-top hover:bg-neutral-50/70">
                                <td class="p-4 font-mono font-bold text-blue-700">{{ $criterion->code }}</td>
                                <td class="p-4 font-bold text-neutral-900">{{ $criterion->name }}</td>
                                <td class="max-w-xs p-4 leading-5 text-neutral-600">{{ $businessDescription }}</td>
                                <td class="p-4">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase {{ $criterion->type === 'benefit' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $criterion->type === 'benefit' ? 'Benefit' : 'Cost' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="block text-neutral-700">{{ match ($criterion->code) {
                                        'C1' => 'Detail transaksi penjualan lunas',
                                        'C2' => 'Stok produk aktual',
                                        'C3' => 'Waktu pengadaan supplier (hari)',
                                        default => 'Data produk',
                                    } }}</span>
                                    <span class="mt-1 block font-mono text-[10px] text-neutral-400">{{ $criterion->data_source }}</span>
                                </td>
                                <td class="p-4">
                                    <label class="sr-only" for="weight-{{ $criterion->id }}">Bobot {{ $criterion->name }}</label>
                                    <div class="relative">
                                        <input id="weight-{{ $criterion->id }}" type="number" step="0.0001" min="0" max="1"
                                            name="weights[{{ $criterion->id }}]" value="{{ old('weights.'.$criterion->id, number_format((float) $criterion->weight, 4, '.', '')) }}" data-code="{{ $criterion->code }}" data-saved-weight="{{ number_format((float) $criterion->weight, 4, '.', '') }}" data-weight-input required
                                            @disabled(old('weights') === null)
                                            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 pr-9 font-mono text-xs font-bold text-neutral-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:cursor-not-allowed disabled:bg-neutral-100 disabled:text-neutral-500">
                                        <span class="pointer-events-none absolute right-3 top-2 text-neutral-400">W</span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold {{ $isComplete ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        <i data-lucide="{{ $isComplete ? 'check' : 'alert-circle' }}" class="h-3 w-3"></i>
                                        {{ $isComplete ? 'Lengkap' : 'Perlu dilengkapi' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-4 border-t border-neutral-100 p-5 lg:flex-row lg:items-center lg:justify-between">
                <p class="max-w-xl text-xs leading-5 text-neutral-500">
                    Benefit berarti nilai yang lebih tinggi lebih baik; cost berarti nilai yang lebih rendah lebih baik.
                    Skor TOPSIS adalah prioritas relatif, bukan jumlah unit pembelian.
                </p>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <button type="button" data-weight-edit-toggle class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-xs font-bold text-blue-800 transition hover:bg-blue-100">
                        Edit Bobot
                    </button>
                    <button type="submit" data-weight-save @class([
                        'hidden' => old('weights') === null,
                        'inline-flex items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-blue-800',
                    ])>
                        <i data-lucide="save" class="h-4 w-4"></i>
                        Simpan Perubahan Bobot
                    </button>
                    <button id="run-topsis-button" type="submit" form="run-topsis" disabled
                        class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold text-white transition disabled:cursor-not-allowed disabled:bg-neutral-400 enabled:bg-slate-900 enabled:hover:bg-slate-800">
                        <i data-lucide="play" class="h-4 w-4"></i>
                        Jalankan Perhitungan TOPSIS
                    </button>
                    <a href="{{ route('admin.topsis.history') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-neutral-200 px-4 py-2.5 text-xs font-bold text-neutral-700 transition hover:bg-neutral-50">
                        Lihat Riwayat
                    </a>
                </div>
            </div>
        </form>

        <form id="run-topsis" action="{{ route('admin.topsis.calculate.run') }}" method="POST" class="hidden">
            @csrf
        </form>
    </section>

    @if ($missingAlternatives !== [] || $productsMissingLeadTime->isNotEmpty() || $productsWithInvalidStock->isNotEmpty())
        <section role="status" class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <h2 class="font-bold text-amber-950">Data perlu dilengkapi sebelum perhitungan</h2>
            <ul class="mt-2 space-y-1 text-sm text-amber-900">
                @if ($missingAlternatives !== [])
                    <li>Produk aktif belum ditemukan: {{ implode(', ', $missingAlternatives) }}. Pastikan produk tersedia di katalog.</li>
                @endif
                @if ($productsMissingLeadTime->isNotEmpty())
                    <li>Waktu pengadaan supplier belum diisi: {{ $productsMissingLeadTime->pluck('name')->implode(', ') }}.</li>
                @endif
                @if ($productsWithInvalidStock->isNotEmpty())
                    <li>Stok tidak valid: {{ $productsWithInvalidStock->pluck('name')->implode(', ') }}.</li>
                @endif
            </ul>
            <a href="{{ route('admin.products') }}" class="mt-3 inline-flex text-xs font-bold text-amber-950 underline underline-offset-2">Buka pengelolaan produk</a>
        </section>
    @endif
</div>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.querySelector('[data-topsis-weights]');
            const calculateButton = document.getElementById('run-topsis-button');
            const totalElement = document.getElementById('topsis-weight-total');
            const statusElement = document.getElementById('topsis-weight-status');
            const errorElement = document.getElementById('topsis-weight-error');
            const editToggle = form?.querySelector('[data-weight-edit-toggle]');
            const saveButton = form?.querySelector('[data-weight-save]');

            if (!form || !calculateButton || !totalElement || !statusElement || !errorElement || !editToggle || !saveButton) {
                return;
            }

            const inputs = [...form.querySelectorAll('[data-weight-input]')];
            const setEditing = (isEditing) => {
                form.dataset.editing = isEditing ? 'true' : 'false';
                inputs.forEach((input) => {
                    input.disabled = !isEditing;
                    if (!isEditing) {
                        input.value = input.dataset.savedWeight ?? input.value;
                    }
                });
                saveButton.classList.toggle('hidden', !isEditing);
                editToggle.textContent = isEditing ? 'Batal Edit' : 'Edit Bobot';
                errorElement.classList.add('hidden');
                updateWeightSummary();

                if (isEditing) {
                    inputs[0]?.focus();
                }
            };
            const numberFormat = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });
            const parseWeightUnits = (value) => {
                const normalizedValue = value.trim();
                if (!/^\d+(?:\.\d{1,4})?$/.test(normalizedValue)) {
                    return null;
                }

                const [whole, fraction = ''] = normalizedValue.split('.');
                const units = Number(whole) * 10000 + Number((fraction + '0000').slice(0, 4));
                return Number.isSafeInteger(units) && units <= 10000 ? units : null;
            };

            const updateWeightSummary = () => {
                const weights = inputs.map((input) => parseWeightUnits(input.value));
                const valuesAreValid = weights.every((weight) => weight !== null);
                const totalUnits = valuesAreValid ? weights.reduce((total, weight) => total + weight, 0) : 0;
                const totalIsValid = valuesAreValid && totalUnits === 10000;
                const hasUnsavedWeights = inputs.some((input) => {
                    const currentWeight = parseWeightUnits(input.value);
                    const savedWeight = parseWeightUnits(input.dataset.savedWeight ?? '');
                    return currentWeight === null || currentWeight !== savedWeight;
                });
                const isValid = totalIsValid;

                totalElement.textContent = `${numberFormat.format(totalUnits / 100)}%`;
                totalElement.classList.toggle('bg-emerald-100', isValid);
                totalElement.classList.toggle('text-emerald-800', isValid);
                totalElement.classList.toggle('bg-red-100', !isValid);
                totalElement.classList.toggle('text-red-800', !isValid);
                statusElement.textContent = isValid ? 'Valid' : 'Tidak Valid';
                statusElement.classList.toggle('text-emerald-700', isValid);
                statusElement.classList.toggle('text-red-700', !isValid);
                errorElement.textContent = valuesAreValid
                    ? (totalIsValid ? '' : 'Total bobot harus tepat 100% sebelum perubahan disimpan atau perhitungan dijalankan.')
                    : 'Masukkan setiap bobot sebagai angka non-negatif dengan maksimal 4 angka desimal dan tidak lebih dari 1,0000.';
                errorElement.classList.toggle('hidden', isValid);

                inputs.forEach((input) => {
                    const units = parseWeightUnits(input.value);
                    const display = document.querySelector(`[data-weight-display="${input.dataset.code}"]`);
                    if (display && units !== null) {
                        display.textContent = `${numberFormat.format(units / 100)}%`;
                    }
                });

                calculateButton.disabled = form.dataset.baseValid !== 'true'
                    || !isValid
                    || hasUnsavedWeights;
                calculateButton.title = hasUnsavedWeights
                    ? 'Simpan perubahan bobot sebelum menjalankan perhitungan.'
                    : (!isValid ? 'Total bobot harus tepat 100%.' : '');
            };

            editToggle.addEventListener('click', () => {
                setEditing(form.dataset.editing !== 'true');
            });

            form.addEventListener('submit', (event) => {
                if (form.dataset.editing !== 'true') {
                    event.preventDefault();
                    return;
                }

                const weights = inputs.map((input) => parseWeightUnits(input.value));
                const totalIsValid = weights.every((weight) => weight !== null)
                    && weights.reduce((total, weight) => total + weight, 0) === 10000;

                if (!totalIsValid) {
                    event.preventDefault();
                    updateWeightSummary();
                    errorElement.classList.remove('hidden');
                    inputs.find((input) => parseWeightUnits(input.value) === null)?.focus();
                }
            });

            inputs.forEach((input) => input.addEventListener('input', updateWeightSummary));
            inputs.forEach((input) => {
                input.addEventListener('input', () => {
                    errorElement.classList.add('hidden');
                });
            });
            updateWeightSummary();
        })();
    </script>
@endpush
