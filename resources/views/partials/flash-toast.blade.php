@if (session()->has('success') || session()->has('error') || session()->has('status'))
    @php($isError = session()->has('error'))
    @php($message = $isError ? session('error') : (session('success') ?? session('status')))

    <div
        data-flash-toast
        role="{{ $isError ? 'alert' : 'status' }}"
        aria-live="{{ $isError ? 'assertive' : 'polite' }}"
        class="fixed top-5 left-4 right-4 sm:left-auto sm:right-6 sm:w-auto sm:max-w-md z-[9999] transition-all duration-300 ease-out"
        style="opacity: 0; transform: translateY(-16px) scale(0.96);"
    >
        <div class="relative overflow-hidden rounded-2xl border backdrop-blur-xl p-3.5 sm:p-4 shadow-2xl flex items-start gap-3.5 {{ $isError ? 'border-red-500/40 bg-gradient-to-r from-red-950/90 via-slate-900/95 to-slate-900/95 text-red-100 shadow-red-950/40' : 'border-emerald-500/40 bg-gradient-to-r from-emerald-950/90 via-slate-900/95 to-slate-900/95 text-emerald-100 shadow-emerald-950/40' }}">
            
            <!-- Ambient Glow -->
            <div class="absolute -top-10 -left-10 w-24 h-24 rounded-full blur-xl pointer-events-none {{ $isError ? 'bg-red-500/20' : 'bg-emerald-500/20' }}"></div>

            <!-- Glowing Icon Badge -->
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 shadow-sm border {{ $isError ? 'bg-red-500/15 border-red-500/30 text-red-400 shadow-red-500/20' : 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400 shadow-emerald-500/20' }}">
                <i data-lucide="{{ $isError ? 'alert-triangle' : 'check-circle-2' }}" class="w-5 h-5"></i>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0 pt-0.5">
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider font-heading {{ $isError ? 'text-red-400' : 'text-emerald-400' }}">
                        {{ $isError ? 'Pemberitahuan' : 'Berhasil' }}
                    </span>
                    <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[9px] font-medium border {{ $isError ? 'bg-red-500/10 text-red-300 border-red-500/20' : 'bg-emerald-500/10 text-emerald-300 border-emerald-500/20' }}">
                        Sistem
                    </span>
                </div>
                <p class="text-xs font-semibold text-white/95 leading-snug tracking-tight">
                    {{ $message }}
                </p>
            </div>

            <!-- Close Button -->
            <button
                type="button"
                data-dismiss-toast
                aria-label="Tutup notifikasi"
                class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors shrink-0 -mr-1 -mt-1 cursor-pointer"
            >
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>

            <!-- 1-Second Animated Progress Bar -->
            <div class="absolute bottom-0 left-0 right-0 h-[2.5px] bg-white/10 overflow-hidden">
                <div id="toastDurationProgress" class="h-full w-full {{ $isError ? 'bg-gradient-to-r from-red-500 to-rose-400' : 'bg-gradient-to-r from-emerald-400 to-teal-300' }} transition-all ease-linear" style="width: 100%;"></div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const toast = document.querySelector('[data-flash-toast]');
            if (!toast) return;

            // Trigger enter animation smoothly
            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0) scale(1)';
            });

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Animate progress bar across 1 second
            const progressBar = document.getElementById('toastDurationProgress');
            if (progressBar) {
                requestAnimationFrame(() => {
                    progressBar.style.transitionDuration = '1000ms';
                    progressBar.style.width = '0%';
                });
            }

            const dismissToast = () => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-14px) scale(0.95)';
                window.setTimeout(() => toast.remove(), 250);
            };

            const dismissButton = toast.querySelector('[data-dismiss-toast]');
            const duration = 1000; // Hilang setelah 1 detik
            const timeout = window.setTimeout(dismissToast, duration);

            if (dismissButton) {
                dismissButton.addEventListener('click', () => {
                    window.clearTimeout(timeout);
                    dismissToast();
                });
            }
        })();
    </script>
@endif
