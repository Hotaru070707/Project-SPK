<style>
@keyframes cartBounceAnimation {
    0% { transform: scale(1); }
    25% { transform: scale(1.35) rotate(-10deg); }
    50% { transform: scale(1.18) rotate(6deg); }
    75% { transform: scale(1.06) rotate(-2deg); }
    100% { transform: scale(1) rotate(0deg); }
}

@keyframes counterPopAnimation {
    0% { transform: scale(0.5); }
    50% { transform: scale(1.45); }
    100% { transform: scale(1); }
}

@keyframes rippleEffect {
    0% { transform: scale(0.8); opacity: 0.9; }
    100% { transform: scale(2.2); opacity: 0; }
}

.animate-cart-bounce {
    animation: cartBounceAnimation 0.55s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

.animate-counter-pop {
    animation: counterPopAnimation 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

.flying-cart-clone {
    position: fixed;
    z-index: 999999;
    pointer-events: none;
    border-radius: 9999px;
    box-shadow: 0 12px 28px -4px rgba(16, 185, 129, 0.45), 0 0 0 2px rgba(16, 185, 129, 0.8);
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    transition: left 0.65s cubic-bezier(0.2, 0.8, 0.25, 1),
                top 0.65s cubic-bezier(0.35, 0, 0.25, 1),
                transform 0.65s cubic-bezier(0.2, 0.8, 0.25, 1),
                opacity 0.65s ease-in;
    will-change: left, top, transform, opacity;
}

.dark .flying-cart-clone {
    background: #0f172a;
    box-shadow: 0 12px 28px -4px rgba(16, 185, 129, 0.6), 0 0 0 2px rgba(52, 211, 153, 0.9);
}
</style>

<script>
(function() {
    let cartToastTimer = null;

    // Toast notifikasi melayang yang tampil selama 1 detik (1000ms)
    function showCartQuickToast(message, isSuccess = true) {
        let toast = document.getElementById('cart-quick-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'cart-quick-toast';
            toast.setAttribute('role', 'status');
            toast.setAttribute('aria-live', 'polite');
            toast.className = 'fixed top-4 right-4 sm:right-6 z-[99999] flex items-center gap-3 px-4 py-3 rounded-2xl shadow-xl border backdrop-blur-md transition-all duration-200 pointer-events-none transform -translate-y-4 opacity-0 scale-95';
            document.body.appendChild(toast);
        }

        // Tampilan toast
        if (isSuccess) {
            toast.className = 'fixed top-4 right-4 sm:right-6 z-[99999] flex items-center gap-3 px-4 py-3 rounded-2xl shadow-xl shadow-emerald-950/20 border border-emerald-500/40 bg-white/95 dark:bg-slate-900/95 text-slate-800 dark:text-slate-100 backdrop-blur-md transition-all duration-200 pointer-events-none transform';
            toast.innerHTML = `
                <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm shadow-emerald-600/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="pr-1">
                    <p class="text-xs font-bold text-emerald-800 dark:text-emerald-400 leading-tight">Berhasil Dimasukkan!</p>
                    <p class="text-[11px] text-neutral-600 dark:text-neutral-300 font-medium leading-tight mt-0.5 line-clamp-1">${message}</p>
                </div>
            `;
        } else {
            toast.className = 'fixed top-4 right-4 sm:right-6 z-[99999] flex items-center gap-3 px-4 py-3 rounded-2xl shadow-xl shadow-red-950/20 border border-red-500/40 bg-white/95 dark:bg-slate-900/95 text-slate-800 dark:text-slate-100 backdrop-blur-md transition-all duration-200 pointer-events-none transform';
            toast.innerHTML = `
                <div class="w-7 h-7 rounded-full bg-red-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="pr-1">
                    <p class="text-xs font-bold text-red-700 dark:text-red-400 leading-tight">Perhatian</p>
                    <p class="text-[11px] text-neutral-600 dark:text-neutral-300 font-medium leading-tight mt-0.5">${message}</p>
                </div>
            `;
        }

        // Tampilkan dengan transisi masuk
        requestAnimationFrame(() => {
            toast.classList.remove('-translate-y-4', 'opacity-0', 'scale-95');
            toast.classList.add('translate-y-0', 'opacity-100', 'scale-100');
        });

        // Reset timer jika ada toast sebelumnya
        if (cartToastTimer) {
            clearTimeout(cartToastTimer);
        }

        // Durasi tepat 1 detik (1000ms) sesuai permintaan
        cartToastTimer = setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100', 'scale-100');
            toast.classList.add('-translate-y-3', 'opacity-0', 'scale-95');
        }, 1000);
    }

    // Fungsi animasi barang terbang menuju ikon keranjang
    function animateFlyToCart(sourceElement, targetElement, onComplete) {
        if (!sourceElement || !targetElement) {
            if (typeof onComplete === 'function') onComplete();
            return;
        }

        const startRect = sourceElement.getBoundingClientRect();
        const targetRect = targetElement.getBoundingClientRect();

        // Elemen klon terbang
        const flyer = document.createElement('div');
        flyer.className = 'flying-cart-clone';

        const size = 52;
        flyer.style.width = size + 'px';
        flyer.style.height = size + 'px';

        // Cari gambar produk atau emoji dari visual produk
        const img = sourceElement.querySelector('img');
        if (img && img.src) {
            flyer.innerHTML = `<img src="${img.src}" alt="product" style="width:100%;height:100%;object-fit:cover;border-radius:9999px;">`;
        } else {
            const textEmoji = sourceElement.textContent.trim();
            const emojiChar = textEmoji.length > 0 && textEmoji.length <= 4 ? textEmoji : '🛍️';
            flyer.innerHTML = `<span style="font-size:26px;display:flex;align-items:center;justify-content:center;width:100%;height:100%;">${emojiChar}</span>`;
        }

        const startX = startRect.left + (startRect.width / 2) - (size / 2);
        const startY = startRect.top + (startRect.height / 2) - (size / 2);

        flyer.style.left = startX + 'px';
        flyer.style.top = startY + 'px';
        flyer.style.transform = 'scale(1) rotate(0deg)';
        flyer.style.opacity = '1';

        document.body.appendChild(flyer);

        // Hitung koordinat target (tengah ikon keranjang di navbar)
        const targetX = targetRect.left + (targetRect.width / 2) - (size / 2);
        const targetY = targetRect.top + (targetRect.height / 2) - (size / 2);

        // Mulai animasi di frame berikutnya
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                flyer.style.left = targetX + 'px';
                flyer.style.top = targetY + 'px';
                flyer.style.transform = 'scale(0.22) rotate(360deg)';
                flyer.style.opacity = '0.35';
            });
        });

        // Waktu terbang 650ms
        setTimeout(() => {
            flyer.remove();

            // Efek membal pada tombol/ikon keranjang
            targetElement.classList.remove('animate-cart-bounce');
            void targetElement.offsetWidth; // trigger reflow
            targetElement.classList.add('animate-cart-bounce');

            if (typeof onComplete === 'function') {
                onComplete();
            }
        }, 650);
    }

    // Intercept semua form tambah ke keranjang
    document.addEventListener('submit', function(e) {
        const form = e.target.closest('form[action*="/akun/keranjang/tambah"]');
        if (!form) return;

        e.preventDefault();

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnHtml = submitBtn ? submitBtn.innerHTML : null;
        if (submitBtn) {
            submitBtn.disabled = true;
        }

        // Cari elemen visual produk sebagai titik awal animasi terbang
        const card = form.closest('.group') || form.closest('.product-card') || form.parentElement;
        const visualSource = (card && (card.querySelector('.aspect-4\\/3') || card.querySelector('img') || card.querySelector('a'))) 
                             || submitBtn 
                             || form;

        // Cari target keranjang di navbar
        const cartButton = document.getElementById('nav-cart-btn') || document.querySelector('a[href*="/akun/keranjang"]');

        const formData = new FormData(form);
        const actionUrl = form.action;

        // Jalankan animasi terbang bersamaan dengan pengiriman data
        animateFlyToCart(visualSource, cartButton, function() {
            // Animasi selesai mencapai keranjang
        });

        // Kirim request AJAX ke controller
        fetch(actionUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': formData.get('_token') || document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        .then(async response => {
            if (response.status === 401) {
                // Pengguna belum login, arahkan ke login
                const data = await response.json();
                window.location.href = data.redirect || '/login';
                return;
            }

            if (!response.ok) {
                const errData = await response.json().catch(() => ({}));
                throw new Error(errData.message || 'Gagal menambahkan produk ke keranjang.');
            }

            return response.json();
        })
        .then(data => {
            if (!data) return;

            // Perbarui counter keranjang
            const counter = document.getElementById('cart-counter');
            if (counter) {
                counter.textContent = data.cart_count;
                counter.classList.remove('animate-counter-pop');
                void counter.offsetWidth;
                counter.classList.add('animate-counter-pop');
            }

            // Tampilkan notifikasi cepat 1 detik
            showCartQuickToast(data.message || 'Produk berhasil ditambahkan ke keranjang!', true);
        })
        .catch(err => {
            console.error('Cart add error:', err);
            showCartQuickToast(err.message || 'Terjadi kesalahan saat menambahkan ke keranjang.', false);
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                if (originalBtnHtml) {
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }
        });
    });
})();
</script>
