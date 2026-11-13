/**
 * School-specific responsive safety net.
 *
 * The parent script owns the drawer, nested menus, keyboard and focus logic.
 * On the school there is no desktop menu toggle, so close an open mobile
 * drawer when a viewport grows to desktop width. This releases body scroll
 * and delegates to the parent's existing cleanup/animations.
 */
(() => {
    'use strict';

    const breakpoint = window.matchMedia('(min-width: 64rem)');
    const drawer = document.querySelector('.ihms-header [data-nav-drawer]');

    if (!drawer) return;

    const closeOnDesktop = () => {
        if (!breakpoint.matches || drawer.hidden || drawer.dataset.open !== 'true') {
            return;
        }

        drawer.querySelector('[data-nav-close]')?.click();
    };

    if (typeof breakpoint.addEventListener === 'function') {
        breakpoint.addEventListener('change', closeOnDesktop);
    } else if (typeof breakpoint.addListener === 'function') {
        breakpoint.addListener(closeOnDesktop);
    }
})();
