import './bootstrap';
import { initProductModal } from './product-modal';
import { initRoleToast } from './role-toast';
import { initAvatarLightbox } from './avatar-lightbox';
import { initSideMenu } from './side-menu';

document.addEventListener('DOMContentLoaded', () => {
    initProductModal();
    initRoleToast();
    initAvatarLightbox();
    initSideMenu();
});
