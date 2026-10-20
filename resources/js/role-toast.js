/**
 * إشعار منبثق (Toast) للبائع/الأدمن عند تصفّح واجهة السوق.
 */
export function initRoleToast() {
    const toast = document.getElementById('role-toast');
    if (!toast) return;

    const close = () => {
        toast.classList.add('translate-y-3', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    };

    // إظهار سلس بعد تحميل الصفحة
    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-3', 'opacity-0');
    });

    toast.querySelector('[data-toast-close]')?.addEventListener('click', close);

    // إخفاء تلقائي بعد 12 ثانية
    const timer = setTimeout(close, 12000);

    toast.addEventListener('mouseenter', () => clearTimeout(timer));
}
