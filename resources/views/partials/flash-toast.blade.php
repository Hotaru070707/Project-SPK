@if (session()->has('success') || session()->has('error'))
    @php($isError = session()->has('error'))
    @php($message = $isError ? session('error') : session('success'))

    <div
        data-flash-toast
        role="{{ $isError ? 'alert' : 'status' }}"
        aria-live="{{ $isError ? 'assertive' : 'polite' }}"
        class="fixed left-4 right-4 top-4 z-[100] mx-auto flex max-w-sm items-start gap-3 rounded-xl border p-4 shadow-lg transition-all duration-300 sm:left-auto sm:right-5 {{ $isError ? 'border-red-200 bg-white text-red-900 dark:border-red-900 dark:bg-slate-900 dark:text-red-200' : 'border-emerald-200 bg-white text-emerald-900 dark:border-emerald-900 dark:bg-slate-900 dark:text-emerald-200' }}"
    >
        <i
            data-lucide="{{ $isError ? 'alert-triangle' : 'check-circle-2' }}"
            class="mt-0.5 h-5 w-5 shrink-0 {{ $isError ? 'text-red-600' : 'text-emerald-600' }}"
        ></i>
        <p class="flex-1 text-sm font-medium">{{ $message }}</p>
        <button
            type="button"
            data-dismiss-toast
            aria-label="Tutup notifikasi"
            class="-mr-1 -mt-1 rounded-lg p-1 text-neutral-400 transition hover:bg-neutral-100 hover:text-neutral-700 dark:hover:bg-slate-800 dark:hover:text-white"
        >
            <i data-lucide="x" class="h-4 w-4"></i>
        </button>
    </div>

    @pushOnce('scripts', 'flash-toast')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const toast = document.querySelector('[data-flash-toast]');

                if (!toast) {
                    return;
                }

                const dismissToast = () => {
                    toast.classList.add('translate-y-2', 'opacity-0');
                    window.setTimeout(() => toast.remove(), 300);
                };
                const dismissButton = toast.querySelector('[data-dismiss-toast]');
                const timeout = window.setTimeout(dismissToast, 5000);

                dismissButton.addEventListener('click', () => {
                    window.clearTimeout(timeout);
                    dismissToast();
                });
            });
        </script>
    @endPushOnce
@endif
