import './bootstrap';
import { initProductModal } from './product-modal';
import { initRoleToast } from './role-toast';

document.addEventListener('DOMContentLoaded', () => {
    initProductModal();
    initRoleToast();
});
