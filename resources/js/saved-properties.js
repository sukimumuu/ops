const SAVED_HEART_SELECTOR = '[data-saved-property-toggle]';
let toastHideTimer = null;

function getToastElement() {
    let toast = document.getElementById('mihom-save-toast');

    if (toast) {
        return toast;
    }

    toast = document.createElement('div');
    toast.id = 'mihom-save-toast';
    toast.className = 'fixed bottom-24 left-1/2 z-[80] flex w-[min(92vw,28rem)] -translate-x-1/2 items-center gap-3 rounded-xl bg-secondary px-4 py-3 font-sans text-sm text-white shadow-xl shadow-secondary/25';
    document.body.appendChild(toast);

    return toast;
}

function showToast(message, options = {}) {
    const toast = getToastElement();
    const isError = options.variant === 'error';

    toast.replaceChildren();
    toast.setAttribute('role', isError ? 'alert' : 'status');
    toast.classList.remove('hidden');
    toast.classList.toggle('bg-secondary', !isError);
    toast.classList.toggle('bg-red-700', isError);

    const text = document.createElement('span');
    text.className = 'flex-1 leading-5';
    text.textContent = message;
    toast.appendChild(text);

    if (options.loginUrl) {
        const loginLink = document.createElement('a');
        loginLink.href = options.loginUrl;
        loginLink.textContent = 'Masuk';
        loginLink.className = 'shrink-0 rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white';
        toast.appendChild(loginLink);
    }

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.setAttribute('aria-label', 'Tutup notifikasi');
    closeButton.textContent = '\u00d7';
    closeButton.className = 'shrink-0 rounded p-1 text-lg leading-none text-white/70 transition hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white';
    closeButton.addEventListener('click', () => toast.classList.add('hidden'));
    toast.appendChild(closeButton);

    window.clearTimeout(toastHideTimer);
    toastHideTimer = window.setTimeout(() => toast.classList.add('hidden'), 5000);
}

function setHeartState(button, saved) {
    const isSaved = Boolean(saved);
    const outlineHeart = button.querySelector('[data-heart-outline]');
    const filledHeart = button.querySelector('[data-heart-filled]');

    button.dataset.saved = isSaved ? 'true' : 'false';
    button.setAttribute('aria-pressed', isSaved ? 'true' : 'false');
    button.setAttribute(
        'aria-label',
        isSaved ? 'Hapus properti ini dari daftar tersimpan' : 'Simpan properti ini ke daftar tersimpan',
    );
    button.title = isSaved ? 'Hapus dari properti tersimpan' : 'Simpan properti';

    outlineHeart?.classList.toggle('hidden', isSaved);
    filledHeart?.classList.toggle('hidden', !isSaved);

    button.classList.toggle('text-primary', isSaved);
    button.classList.toggle('hover:text-primary/80', isSaved);
    button.classList.toggle('text-gray-400', !isSaved);
    button.classList.toggle('hover:text-gray-600', !isSaved);
}

function showRequestError(status, payload, loginUrl) {
    const serverMessage = typeof payload?.message === 'string' ? payload.message : null;

    switch (status) {
        case 401:
            showToast('Silakan masuk untuk menyimpan properti favorit Anda.', {
                variant: 'error',
                loginUrl,
            });
            break;
        case 403:
            showToast('Anda tidak memiliki akses untuk menyimpan properti ini.', { variant: 'error' });
            break;
        case 404:
            showToast('Properti tidak ditemukan atau sudah tidak tersedia.', { variant: 'error' });
            break;
        case 419:
            showToast('Sesi Anda telah berakhir. Muat ulang halaman lalu coba lagi.', { variant: 'error' });
            break;
        case 422:
            showToast(serverMessage || 'Properti tidak dapat disimpan saat ini.', { variant: 'error' });
            break;
        case 429:
            showToast('Terlalu banyak permintaan. Tunggu sebentar lalu coba lagi.', { variant: 'error' });
            break;
        default:
            showToast('Terjadi kesalahan pada server. Silakan coba lagi.', { variant: 'error' });
            break;
    }
}

document.addEventListener('click', async (event) => {
    if (!(event.target instanceof Element)) {
        return;
    }

    const button = event.target.closest(SAVED_HEART_SELECTOR);

    if (!button) {
        return;
    }

    event.preventDefault();
    event.stopPropagation();

    if (button.dataset.pending === 'true') {
        return;
    }

    const propertyId = button.dataset.propertyId;
    const toggleUrl = button.dataset.toggleUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!propertyId || !toggleUrl || !csrfToken) {
        showToast('Fitur simpan properti belum dapat digunakan. Muat ulang halaman.', { variant: 'error' });
        return;
    }

    button.dataset.pending = 'true';
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');

    try {
        const response = await fetch(toggleUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                property_id: propertyId,
            }),
        });

        let payload = null;

        if ((response.headers.get('content-type') || '').includes('application/json')) {
            try {
                payload = await response.json();
            } catch (error) {
                payload = null;
            }
        }

        if (!response.ok) {
            showRequestError(response.status, payload, button.dataset.loginUrl);
            return;
        }

        if (!payload || typeof payload.saved !== 'boolean') {
            showToast('Respons server tidak valid. Silakan muat ulang halaman.', { variant: 'error' });
            return;
        }

        setHeartState(button, payload.saved);
        showToast(
            typeof payload.message === 'string'
                ? payload.message
                : (payload.saved ? 'Properti berhasil disimpan.' : 'Properti dihapus dari daftar tersimpan.'),
            { variant: 'success' },
        );
    } catch (error) {
        showToast('Tidak dapat terhubung ke server. Periksa koneksi Anda lalu coba lagi.', { variant: 'error' });
    } finally {
        button.dataset.pending = 'false';
        button.disabled = false;
        button.removeAttribute('aria-busy');
    }
});
