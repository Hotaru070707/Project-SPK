@extends($layout)

@section('header', 'Profil Saya')

@section('content')
<div class="max-w-4xl mx-auto py-4 sm:py-6 px-4">
    <!-- Breadcrumb / Back button -->
    <div class="mb-5 flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-neutral-500 dark:text-slate-400">
            <a href="{{ $user->isOwner() ? route('admin.dashboard') : ($user->isCashier() ? route('cashier.index') : url('/')) }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition flex items-center gap-1">
                <i data-lucide="home" class="w-3.5 h-3.5"></i> Beranda
            </a>
            <span>/</span>
            <span class="text-neutral-800 dark:text-slate-200 font-semibold">Profil Saya</span>
        </div>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between shadow-sm animate-fade-in">
            <div class="flex items-center gap-2.5">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 text-sm shadow-sm animate-fade-in">
            <div class="flex items-center gap-2 font-semibold mb-1">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 dark:text-red-400"></i>
                <span>Terjadi beberapa kesalahan:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 text-red-700 dark:text-red-400 ml-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-8">
        @if($user->isCustomer())
            <nav aria-label="Navigasi profil" class="flex flex-wrap gap-2">
                <a href="{{ route('profile.edit') }}" aria-current="page" class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white">
                    <i data-lucide="user-round" class="w-4 h-4"></i> Informasi Profil
                </a>
                <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-2 rounded-full border border-neutral-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-2.5 text-sm font-semibold text-neutral-700 dark:text-slate-200 hover:border-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-400 transition">
                    <i data-lucide="receipt-text" class="w-4 h-4"></i> Riwayat Pemesanan
                </a>
            </nav>
        @endif

        <!-- Card: Informasi Akun (Sesuai Desain Screenshot) -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-10 shadow-sm border border-neutral-100 dark:border-slate-800 transition-colors">
            <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-6">Informasi Akun</h2>

            <!-- Form Update Profil -->
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Avatar Section -->
                <div>
                    <div class="flex items-center gap-4">
                        <!-- Avatar Preview Frame -->
                        <div class="relative w-18 h-18 sm:w-20 sm:h-20 rounded-full overflow-hidden bg-neutral-100 dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 shadow-sm shrink-0 flex items-center justify-center">
                            <img id="avatarPreview" 
                                 src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&color=059669&background=ecfdf5&bold=true' }}" 
                                 alt="Avatar {{ $user->name }}" 
                                 class="w-full h-full object-cover">
                        </div>

                        <!-- Avatar Actions -->
                        <div class="flex items-center gap-2.5">
                            <!-- Hidden File Input -->
                            <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="previewAvatar(this)">
                            
                            <!-- Ganti Avatar Button -->
                            <button type="button" onclick="document.getElementById('avatarInput').click()" 
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full border border-emerald-500 text-emerald-600 dark:text-emerald-400 bg-white dark:bg-slate-800 hover:bg-emerald-50/70 dark:hover:bg-slate-700 text-xs sm:text-sm font-semibold transition active:scale-95 shadow-sm">
                                <i data-lucide="edit-3" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                                <span>Ganti Avatar</span>
                            </button>

                            <!-- Delete Avatar Button -->
                            @if($user->avatar)
                                <button type="button" onclick="promptDeleteAvatar()" 
                                        title="Hapus Avatar"
                                        class="w-10 h-10 rounded-full border border-red-300 dark:border-red-800/80 text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/50 flex items-center justify-center transition active:scale-95 shadow-sm">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            @else
                                <button type="button" id="resetPreviewBtn" onclick="resetAvatarPreview()" 
                                        title="Batal Pilih"
                                        style="display: none;"
                                        class="w-10 h-10 rounded-full border border-red-300 dark:border-red-800/80 text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/50 flex items-center justify-center transition active:scale-95 shadow-sm">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    <!-- Avatar Help Text -->
                    <p class="text-xs text-neutral-400 dark:text-slate-400 font-normal mt-2.5">
                        Format: JPG, PNG, WEBP. Maksimal 10MB
                    </p>
                </div>

                <!-- Input Fields Grid -->
                <div class="space-y-4 pt-2">
                    <!-- Row 1: Nama Lengkap & Nomor Telepon -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Nama Lengkap -->
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 dark:text-slate-300 mb-1.5">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="inputName"
                                   value="{{ old('name', $user->name) }}" 
                                   required 
                                   placeholder="Masukkan nama lengkap"
                                   class="w-full px-5 py-3 rounded-full border border-neutral-200 dark:border-slate-700 text-sm text-neutral-800 dark:text-white placeholder-neutral-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition bg-white dark:bg-slate-800">
                        </div>

                        <!-- Nomor Telepon -->
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 dark:text-slate-300 mb-1.5">
                                Nomor Telepon
                            </label>
                            <div class="flex items-center gap-2.5">
                                <!-- Country code dropdown badge -->
                                <div class="flex items-center gap-1 px-4 py-3 rounded-full border border-neutral-200 dark:border-slate-700 bg-neutral-50/60 dark:bg-slate-800 text-xs font-bold text-neutral-700 dark:text-slate-300 shrink-0 select-none shadow-sm">
                                    <span>+62</span>
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-neutral-400 dark:text-slate-500"></i>
                                </div>
                                <!-- Phone input -->
                                <input type="text" 
                                       name="phone" 
                                       id="inputPhone"
                                       value="{{ old('phone', $phone) }}" 
                                       placeholder="88222010306"
                                       class="w-full px-5 py-3 rounded-full border border-neutral-200 dark:border-slate-700 text-sm text-neutral-800 dark:text-white placeholder-neutral-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition bg-white dark:bg-slate-800">
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Email -->
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 dark:text-slate-300 mb-1.5">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               id="inputEmail"
                               value="{{ old('email', $user->email) }}" 
                               required 
                               placeholder="nama@email.com"
                               class="w-full px-5 py-3 rounded-full border border-neutral-200 dark:border-slate-700 text-sm text-neutral-800 dark:text-white placeholder-neutral-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition bg-white dark:bg-slate-800">
                    </div>

                    <!-- Row 3: Alamat -->
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 dark:text-slate-300 mb-1.5">
                            Alamat
                        </label>
                        <input type="text" 
                               name="address" 
                               id="inputAddress"
                               value="{{ old('address', $user->address) }}" 
                               placeholder="Tuparev, Cirebon, Jawa Barat."
                               class="w-full px-5 py-3 rounded-full border border-neutral-200 dark:border-slate-700 text-sm text-neutral-800 dark:text-white placeholder-neutral-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition bg-white dark:bg-slate-800">
                    </div>
                </div>

                <!-- Action Buttons: Simpan & Keluar Akun -->
                <div class="pt-2 flex flex-wrap items-center gap-3">
                    <button type="button" 
                            onclick="promptSaveProfile()"
                            class="inline-flex items-center justify-center px-8 py-3 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm transition-all shadow-sm hover:shadow active:scale-95">
                        Simpan Perubahan
                    </button>

                    <button type="button" 
                            onclick="promptLogout()" 
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full border border-red-200 dark:border-red-900/60 hover:border-red-300 dark:hover:border-red-800 bg-red-50 dark:bg-red-950/30 hover:bg-red-100/80 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 font-semibold text-sm transition-all active:scale-95">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span>Keluar dari Akun</span>
                    </button>
                </div>
            </form>

            <!-- Hidden Form for Logout -->
            <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>

            <!-- Hidden Form for Avatar Deletion -->
            @if($user->avatar)
                <form id="deleteAvatarForm" action="{{ route('profile.avatar.destroy') }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- CUSTOM IN-APP CONFIRMATION POPUP MODAL     -->
<!-- (Bukan alert bawaan browser localhost)     -->
<!-- ========================================== -->
<div id="customConfirmModal" class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-200">
    <div id="modalBox" class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 max-w-sm w-full shadow-2xl border border-neutral-100 dark:border-slate-800 text-center transform scale-95 transition-all duration-200">
        <!-- Icon Badge Container -->
        <div id="modalIconContainer" class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 transition-colors shadow-inner">
            <span id="modalIconWrapper">
                <i id="modalIcon" data-lucide="help-circle" class="w-8 h-8"></i>
            </span>
        </div>
        
        <!-- Title & Description -->
        <h3 id="modalTitle" class="text-lg font-bold text-neutral-900 dark:text-white mb-2">Konfirmasi Tindakan</h3>
        <p id="modalMessage" class="text-xs sm:text-sm text-neutral-500 dark:text-slate-400 mb-6 leading-relaxed">
            Apakah Anda yakin ingin melanjutkan tindakan ini?
        </p>

        <!-- Buttons -->
        <div class="flex items-center justify-center gap-3">
            <button type="button" onclick="closeConfirmModal()" 
                    class="flex-1 px-5 py-2.5 rounded-full border border-neutral-200 dark:border-slate-700 hover:bg-neutral-100 dark:hover:bg-slate-800 text-neutral-700 dark:text-slate-300 font-semibold text-xs sm:text-sm transition active:scale-95">
                Batal
            </button>
            <button type="button" id="modalConfirmBtn" 
                    class="flex-1 px-5 py-2.5 rounded-full font-semibold text-xs sm:text-sm transition shadow-sm active:scale-95">
                Lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
    const originalAvatarSrc = "{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&color=059669&background=ecfdf5&bold=true' }}";
    let pendingConfirmAction = null;

    // --- In-App Custom Confirmation Modal Functions ---
    function openConfirmModal({ title, message, iconName, iconBg, iconColor, confirmBtnText, confirmBtnClass, onConfirm }) {
        const modal = document.getElementById('customConfirmModal');
        const box = document.getElementById('modalBox');
        const iconContainer = document.getElementById('modalIconContainer');
        const iconWrapper = document.getElementById('modalIconWrapper');
        const titleEl = document.getElementById('modalTitle');
        const msgEl = document.getElementById('modalMessage');
        const confirmBtn = document.getElementById('modalConfirmBtn');

        titleEl.textContent = title;
        msgEl.textContent = message;

        // Set Icon Style
        iconContainer.className = `w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 transition-colors ${iconBg}`;
        iconWrapper.innerHTML = `<i data-lucide="${iconName}" class="w-8 h-8 ${iconColor}"></i>`;
        lucide.createIcons();

        // Set Confirm Button Style
        confirmBtn.textContent = confirmBtnText;
        confirmBtn.className = `flex-1 px-5 py-2.5 rounded-full font-semibold text-xs sm:text-sm transition shadow-sm active:scale-95 ${confirmBtnClass}`;

        pendingConfirmAction = onConfirm;

        // Show modal with animation
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        box.classList.remove('scale-95');
        box.classList.add('scale-100');
    }

    function closeConfirmModal() {
        const modal = document.getElementById('customConfirmModal');
        const box = document.getElementById('modalBox');

        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100');
        box.classList.add('scale-95');
        box.classList.remove('scale-100');
        pendingConfirmAction = null;
    }

    document.getElementById('modalConfirmBtn').addEventListener('click', function() {
        if (typeof pendingConfirmAction === 'function') {
            const action = pendingConfirmAction;
            closeConfirmModal();
            action();
        }
    });

    // Close modal when clicking outside box
    document.getElementById('customConfirmModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeConfirmModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeConfirmModal();
        }
    });

    // --- Action Triggers (Using In-App Modal) ---
    function promptSaveProfile() {
        const form = document.getElementById('profileForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        openConfirmModal({
            title: 'Simpan Perubahan?',
            message: 'Apakah Anda yakin ingin menyimpan perubahan data profil Anda?',
            iconName: 'check-circle-2',
            iconBg: 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400',
            iconColor: 'text-emerald-600 dark:text-emerald-400',
            confirmBtnText: 'Ya, Simpan',
            confirmBtnClass: 'bg-emerald-600 hover:bg-emerald-700 text-white',
            onConfirm: function() {
                form.submit();
            }
        });
    }

    function promptLogout() {
        openLogoutModal();
    }

    function promptDeleteAvatar() {
        openConfirmModal({
            title: 'Hapus Foto Avatar?',
            message: 'Apakah Anda yakin ingin menghapus foto profil Anda?',
            iconName: 'trash-2',
            iconBg: 'bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400',
            iconColor: 'text-red-600 dark:text-red-400',
            confirmBtnText: 'Ya, Hapus',
            confirmBtnClass: 'bg-red-600 hover:bg-red-700 text-white',
            onConfirm: function() {
                document.getElementById('deleteAvatarForm').submit();
            }
        });
    }

    // --- Avatar Preview & Reset ---
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Check max 10MB
            if (file.size > 10 * 1024 * 1024) {
                openConfirmModal({
                    title: 'Ukuran Terlalu Besar',
                    message: 'Ukuran file foto maksimal adalah 10MB! Silakan pilih foto lain.',
                    iconName: 'alert-triangle',
                    iconBg: 'bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400',
                    iconColor: 'text-amber-600 dark:text-amber-400',
                    confirmBtnText: 'Mengerti',
                    confirmBtnClass: 'bg-amber-600 hover:bg-amber-700 text-white',
                    onConfirm: function() {
                        input.value = '';
                    }
                });
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
                const resetBtn = document.getElementById('resetPreviewBtn');
                if (resetBtn) {
                    resetBtn.style.display = 'flex';
                }
            };
            reader.readAsDataURL(file);
        }
    }

    function resetAvatarPreview() {
        const input = document.getElementById('avatarInput');
        if (input) input.value = '';
        document.getElementById('avatarPreview').src = originalAvatarSrc;
        const resetBtn = document.getElementById('resetPreviewBtn');
        if (resetBtn) resetBtn.style.display = 'none';
    }
</script>
@endsection
