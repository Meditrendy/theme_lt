(() => {
  const root = document.documentElement;
  const desktopQuery = window.matchMedia('(min-width: 980px)');
  const menuItemSelector = '.x-menu-inline > .menu-item-has-children';
  const closeTimers = new WeakMap();

  const updateMenuTop = () => {
    if (!desktopQuery.matches) {
      root.style.removeProperty('--mt-desktop-menu-top');
      return;
    }

    const bar = document.querySelector('.x-bar-top.x-bar-h');

    if (!bar) {
      root.style.removeProperty('--mt-desktop-menu-top');
      return;
    }

    const rect = bar.getBoundingClientRect();
    root.style.setProperty('--mt-desktop-menu-top', `${Math.max(0, Math.round(rect.bottom))}px`);
  };

  const scheduleUpdate = () => {
    window.requestAnimationFrame(updateMenuTop);
  };

  const closeMenuItem = (item) => {
    if (!item) return;

    const timer = closeTimers.get(item);

    if (timer) {
      window.clearTimeout(timer);
      closeTimers.delete(item);
    }

    item.classList.remove('mt-desktop-menu-open');
    item.classList.remove('x-active', 'x-active-animate');

    item.querySelectorAll(':scope > .sub-menu.x-dropdown').forEach((menu) => {
      menu.classList.remove('x-active', 'x-active-animate');
      menu.setAttribute('aria-hidden', 'true');
    });
  };

  const openMenuItem = (item) => {
    if (!desktopQuery.matches || !item) return;

    const timer = closeTimers.get(item);

    if (timer) {
      window.clearTimeout(timer);
      closeTimers.delete(item);
    }

    closeSiblingMenus(item);
    item.classList.add('mt-desktop-menu-open');
    updateMenuTop();
  };

  const scheduleMenuClose = (item) => {
    if (!desktopQuery.matches || !item) return;

    const previousTimer = closeTimers.get(item);

    if (previousTimer) {
      window.clearTimeout(previousTimer);
    }

    closeTimers.set(item, window.setTimeout(() => {
      closeTimers.delete(item);

      if (!item.matches(':hover') && !item.matches(':focus-within')) {
        closeMenuItem(item);
      }
    }, 220));
  };

  const closeSiblingMenus = (item) => {
    if (!desktopQuery.matches || !item || !item.parentElement) return;

    item.parentElement.querySelectorAll(':scope > .menu-item-has-children').forEach((sibling) => {
      if (sibling !== item) {
        closeMenuItem(sibling);
      }
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateMenuTop, { once: true });
  } else {
    updateMenuTop();
  }

  window.addEventListener('resize', scheduleUpdate, { passive: true });
  window.addEventListener('scroll', scheduleUpdate, { passive: true });
  document.addEventListener('pointerenter', (event) => {
    const item = event.target.closest && event.target.closest(menuItemSelector);

    if (item) {
      openMenuItem(item);
    }
  }, true);
  document.addEventListener('pointerleave', (event) => {
    const item = event.target.closest && event.target.closest(menuItemSelector);

    scheduleMenuClose(item);
  }, true);
  document.addEventListener('focusin', (event) => {
    const item = event.target.closest && event.target.closest(menuItemSelector);

    if (item) {
      openMenuItem(item);
    }
  });
})();
