/**
 * تكبير الصورة الشخصية في منتصف الشاشة عند الضغط عليها.
 */
export function initAvatarLightbox() {
    const box = document.getElementById('avatar-lightbox');
    if (!box) return;

    const img = box.querySelector('[data-lightbox-image]');
    const close = () => {
        box.style.display = 'none';
        img.removeAttribute('src');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('[data-avatar]').forEach((el) => {
        el.addEventListener('click', (event) => {
            const src = el.getAttribute('data-avatar');
            if (!src) return;
            event.preventDefault();
            img.src = src;
            box.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    });

    box.addEventListener('click', (event) => {
        if (event.target === box || event.target.closest('[data-lightbox-close]')) close();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && box.style.display === 'flex') close();
    });
}
