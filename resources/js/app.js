import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', function () {
    const userMeta = document.querySelector('meta[name="user-id"]');
    if (!userMeta) {
        const meta = document.createElement('meta');
        meta.name = 'user-id';
        meta.content = document.querySelector('meta[name="user-id"]')?.content || '';
        document.head.appendChild(meta);
    }
});
