@extends('layouts.admin', ['title' => 'Akun Kasir - Mobeli', 'header' => 'Manajemen Pengguna Kasir'])

@section('content')
<div class="space-y-6">
    <form action="{{ route('admin.users.store') }}" method="POST" class="grid grid-cols-1 gap-3 rounded-2xl border border-neutral-200 bg-white p-5 shadow-xs sm:grid-cols-2 xl:grid-cols-5 xl:items-end">
        @csrf
        <div><label for="name" class="mb-1 block text-xs font-semibold text-neutral-700">Nama kasir</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="255" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-xs"></div>
        <div><label for="email" class="mb-1 block text-xs font-semibold text-neutral-700">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-xs"></div>
        <div><label for="phone" class="mb-1 block text-xs font-semibold text-neutral-700">Nomor telepon</label><input id="phone" name="phone" value="{{ old('phone') }}" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-xs"></div>
        <div><label for="password" class="mb-1 block text-xs font-semibold text-neutral-700">Kata sandi awal</label><input id="password" name="password" type="password" minlength="8" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-xs"></div>
        <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white hover:bg-blue-700">Tambah Akun Kasir</button>
    </form>

    <div class="space-y-3">
        @forelse($cashiers as $cashier)
            <form action="{{ route('admin.users.update', $cashier->id) }}" method="POST" class="grid grid-cols-1 gap-3 rounded-2xl border border-neutral-200 bg-white p-4 sm:grid-cols-2 xl:grid-cols-6 xl:items-end">
                @csrf
                @method('PUT')
                <div><label class="mb-1 block text-[11px] font-semibold text-neutral-500">Nama</label><input name="name" value="{{ $cashier->name }}" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-xs"></div>
                <div><label class="mb-1 block text-[11px] font-semibold text-neutral-500">Email</label><input name="email" type="email" value="{{ $cashier->email }}" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-xs"></div>
                <div><label class="mb-1 block text-[11px] font-semibold text-neutral-500">Telepon</label><input name="phone" value="{{ $cashier->phone }}" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-xs"></div>
                <div><label class="mb-1 block text-[11px] font-semibold text-neutral-500">Kata sandi baru (opsional)</label><input name="password" type="password" minlength="8" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-xs"></div>
                <div><label class="mb-1 block text-[11px] font-semibold text-neutral-500">Status akun</label><select name="is_active" required class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-xs"><option value="1" @selected($cashier->is_active)>Aktif</option><option value="0" @selected(!$cashier->is_active)>Nonaktif</option></select></div>
                <button type="submit" class="rounded-lg border border-neutral-300 px-4 py-2 text-xs font-bold text-neutral-700 hover:bg-neutral-50">Simpan Perubahan</button>
            </form>
        @empty
            <div class="rounded-2xl border border-neutral-200 bg-white p-8 text-center text-xs text-neutral-500">Belum ada akun kasir.</div>
        @endforelse
        <div>{{ $cashiers->links() }}</div>
    </div>
</div>
@endsection
