@auth
<!-- Modal Konfirmasi Logout Global -->
<div id="logoutConfirmModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-200">
    <div id="logoutModalBox" class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 max-w-sm w-full shadow-2xl border border-neutral-100 dark:border-slate-800 text-center transform scale-95 transition-all duration-200">
        <!-- Icon Badge -->
        <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4 bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 shadow-inner">
            <i data-lucide="log-out" class="w-8 h-8"></i>
        </div>
        
        <!-- Title & Description -->
        <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-2">Konfirmasi Keluar</h3>
        <p class="text-xs sm:text-sm text-neutral-500 dark:text-slate-400 mb-6 leading-relaxed">
            Apakah Anda yakin ingin keluar dari akun ini? Anda perlu masuk kembali untuk mengakses layanan akun.
        </p>

        <!-- Buttons -->
        <div class="flex items-center justify-center gap-3">
            <button type="button" onclick="closeLogoutModal()" 
                    class="flex-1 px-5 py-2.5 rounded-full border border-neutral-200 dark:border-slate-700 hover:bg-neutral-100 dark:hover:bg-slate-800 text-neutral-700 dark:text-slate-300 font-semibold text-xs sm:text-sm transition active:scale-95">
                Batal
            </button>
            <button type="button" onclick="submitLogout()" 
                    class="flex-1 px-5 py-2.5 rounded-full bg-red-600 hover:bg-red-700 text-white font-semibold text-xs sm:text-sm transition shadow-sm shadow-red-600/30 active:scale-95">
                Ya, Keluar
            </button>
        </div>
    </div>
</div>

<form id="globalLogoutForm" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>

<script>
    function openLogoutModal(event) {
        if (event) {
            event.preventDefault();
        }
        const modal = document.getElementById('logoutConfirmModal');
        const box = document.getElementById('logoutModalBox');
        if (!modal || !box) return;

        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        box.classList.remove('scale-95');
        box.classList.add('scale-100');
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function closeLogoutModal() {
        const modal = document.getElementById('logoutConfirmModal');
        const box = document.getElementById('logoutModalBox');
        if (!modal || !box) return;

        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100');
        box.classList.add('scale-95');
        box.classList.remove('scale-100');
    }

    function submitLogout() {
        const form = document.getElementById('globalLogoutForm');
        if (form) {
            form.submit();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('logoutConfirmModal');
        if (!modal) return;

        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogoutModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('pointer-events-none')) {
                closeLogoutModal();
            }
        });
    });
</script>
@endauth
