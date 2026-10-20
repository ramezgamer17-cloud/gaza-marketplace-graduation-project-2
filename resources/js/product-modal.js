/**
 * نافذة معاينة المنتج (Quick View)
 */
export function initProductModal() {
    const dialog = document.getElementById('product-modal');
    if (!dialog) return;

    const img = dialog.querySelector('[data-modal-image]');
    const placeholder = dialog.querySelector('[data-modal-placeholder]');
    const name = dialog.querySelector('[data-modal-name]');
    const store = dialog.querySelector('[data-modal-store]');
    const storeLink = dialog.querySelector('[data-modal-store-link]');
    const description = dialog.querySelector('[data-modal-description]');
    const price = dialog.querySelector('[data-modal-price]');
    const compare = dialog.querySelector('[data-modal-compare]');
    const discount = dialog.querySelector('[data-modal-discount]');
    const form = dialog.querySelector('[data-modal-form]');
    const guestBox = dialog.querySelector('[data-modal-guest]');
    const closeBtn = dialog.querySelector('[data-modal-close]');

    const open = (data) => {
        if (data.image) {
            img.src = data.image;
            img.classList.remove('hidden');
            placeholder.classList.add('hidden');
        } else {
            img.removeAttribute('src');
            img.classList.add('hidden');
            placeholder.textContent = (data.name || '؟').trim().charAt(0);
            placeholder.classList.remove('hidden');
        }

        name.textContent = data.name || '';
        store.textContent = data.store || '';
        description.textContent = data.description || 'لا يوجد وصف لهذا المنتج.';

        if (data.storeUrl) {
            storeLink.href = data.storeUrl;
            storeLink.classList.remove('hidden');
        } else {
            storeLink.classList.add('hidden');
        }

        price.textContent = data.price ? `${data.price} شيكل` : '';

        if (data.compare && data.compare !== data.price) {
            compare.textContent = `${data.compare} شيكل`;
            compare.classList.remove('hidden');
            const pct = Math.round(((parseFloat(data.compare) - parseFloat(data.price)) / parseFloat(data.compare)) * 100);
            discount.textContent = `-${pct}%`;
            discount.classList.remove('hidden');
        } else {
            compare.classList.add('hidden');
            discount.classList.add('hidden');
        }

        if (data.authed === '1') {
            form.action = data.addUrl;
            form.classList.remove('hidden');
            guestBox.classList.add('hidden');
        } else {
            form.classList.add('hidden');
            guestBox.classList.remove('hidden');
        }

        dialog.showModal();
    };

    document.querySelectorAll('[data-product]').forEach((card) => {
        card.addEventListener('click', () => open(card.dataset));
    });

    closeBtn?.addEventListener('click', () => dialog.close());

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });
}
