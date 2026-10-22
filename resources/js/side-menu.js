/**
 * القائمة الجانبية (هامبرغر).
 */
export function initSideMenu() {
    const menu = document.getElementById('side-menu');
    const toggles = document.querySelectorAll('[data-menu-toggle]');
    if (!menu) return;

    const open = () => {
        menu.style.display = 'block';
        document.body.style.overflow = 'hidden';
    };

    const close = () => {
        menu.style.display = 'none';
        document.body.style.overflow = '';
    };

    toggles.forEach((btn) => btn.addEventListener('click', open));

    menu.querySelectorAll('[data-menu-close]').forEach((el) => el.addEventListener('click', close));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });
}
