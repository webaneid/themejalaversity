/**
 * Jalaversity — Front-End JavaScript
 *
 * Vanilla ES6+. No jQuery. Loaded deferred in footer.
 * Each feature is isolated in its own init function.
 *
 * @package Jalaversity
 */

document.addEventListener('DOMContentLoaded', () => {
  initStickyHeader();
  initMobileMenu();
  initDesktopSubmenu();
  initNewsTabs();
  initSmoothScroll();
  initArabicParagraphs();
  initCopyLink();
});

/* ── Sticky Header ──────────────────────────────────────────────────── */

function initStickyHeader() {
  const header = document.getElementById('site-header');
  if (!header) return;

  const onScroll = () => {
    header.style.boxShadow = window.scrollY > 20
      ? '0 10px 30px rgba(8,66,46,.10)'
      : 'none';
  };

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
}

/* ── Mobile Menu ────────────────────────────────────────────────────── */

function initMobileMenu() {
  const toggleBtn  = document.getElementById('mobile-menu-toggle');
  const closeBtn   = document.getElementById('mobile-menu-close');
  const backdrop   = document.getElementById('mobile-menu-backdrop');
  const menu       = document.getElementById('mobile-menu');
  const header     = document.getElementById('site-header');

  if (!toggleBtn || !menu) return;

  const isOpen = () => menu.getAttribute('aria-hidden') === 'false';

  const openMenu = () => {
    menu.setAttribute('aria-hidden', 'false');
    menu.classList.add('is-open');
    toggleBtn.setAttribute('aria-expanded', 'true');
    header?.classList.add('menu-open');
    document.body.style.overflow = 'hidden';

    // Focus first focusable element inside panel
    const first = menu.querySelector('a, button');
    first?.focus();
  };

  const closeMenu = () => {
    menu.setAttribute('aria-hidden', 'true');
    menu.classList.remove('is-open');
    toggleBtn.setAttribute('aria-expanded', 'false');
    header?.classList.remove('menu-open');
    document.body.style.overflow = '';
    toggleBtn.focus();
  };

  toggleBtn.addEventListener('click', () => isOpen() ? closeMenu() : openMenu());
  closeBtn?.addEventListener('click', closeMenu);
  backdrop?.addEventListener('click', closeMenu);

  // Close on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isOpen()) closeMenu();
  });

  // Mobile submenu accordion
  menu.querySelectorAll('.submenu-toggle').forEach((btn) => {
    btn.addEventListener('click', () => {
      const li      = btn.closest('.nav-item');
      const subMenu = li?.querySelector('.sub-menu');
      if (!li || !subMenu) return;

      const expanded = btn.getAttribute('aria-expanded') === 'true';

      // Close sibling open submenus first
      menu.querySelectorAll('.nav-item.is-submenu-open').forEach((openLi) => {
        if (openLi !== li) {
          openLi.classList.remove('is-submenu-open');
          openLi.querySelector('.submenu-toggle')?.setAttribute('aria-expanded', 'false');
          openLi.querySelector('.sub-menu')?.setAttribute('aria-hidden', 'true');
        }
      });

      // Toggle current
      if (expanded) {
        li.classList.remove('is-submenu-open');
        btn.setAttribute('aria-expanded', 'false');
        subMenu.setAttribute('aria-hidden', 'true');
      } else {
        li.classList.add('is-submenu-open');
        btn.setAttribute('aria-expanded', 'true');
        subMenu.setAttribute('aria-hidden', 'false');
      }
    });
  });
}

/* ── Desktop Submenu ────────────────────────────────────────────────── */

function initDesktopSubmenu() {
  const navMenu = document.getElementById('primary-menu');
  if (!navMenu) return;

  const items = navMenu.querySelectorAll('.nav-item.has-submenu');

  items.forEach((li) => {
    const toggle  = li.querySelector('.submenu-toggle');
    const subMenu = li.querySelector('.sub-menu');
    if (!toggle || !subMenu) return;

    let closeTimer;

    const open = () => {
      clearTimeout(closeTimer);
      li.classList.add('is-submenu-open');
      toggle.setAttribute('aria-expanded', 'true');
      subMenu.setAttribute('aria-hidden', 'false');
    };

    const close = () => {
      closeTimer = setTimeout(() => {
        li.classList.remove('is-submenu-open');
        toggle.setAttribute('aria-expanded', 'false');
        subMenu.setAttribute('aria-hidden', 'true');
      }, 120);
    };

    // Hover (desktop)
    li.addEventListener('mouseenter', open);
    li.addEventListener('mouseleave', close);
    subMenu.addEventListener('mouseenter', () => clearTimeout(closeTimer));
    subMenu.addEventListener('mouseleave', close);

    // Keyboard / click toggle
    toggle.addEventListener('click', (e) => {
      e.stopPropagation();
      const expanded = toggle.getAttribute('aria-expanded') === 'true';
      expanded ? close() : open();
    });

    // Close if click outside
    document.addEventListener('click', (e) => {
      if (!li.contains(e.target)) {
        li.classList.remove('is-submenu-open');
        toggle.setAttribute('aria-expanded', 'false');
        subMenu.setAttribute('aria-hidden', 'true');
      }
    });
  });
}

/* ── News Tabs ──────────────────────────────────────────────────────── */

function initNewsTabs() {
  const tabBtns  = document.querySelectorAll('[data-tab-target]');
  const tabPanels = document.querySelectorAll('[data-tab-panel]');
  if (!tabBtns.length) return;

  tabBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
      const target = btn.dataset.tabTarget;

      // Deactivate all
      tabBtns.forEach((b) => {
        b.classList.remove('is-active');
        b.setAttribute('aria-selected', 'false');
      });
      tabPanels.forEach((p) => {
        p.hidden = true;
        p.setAttribute('aria-hidden', 'true');
      });

      // Activate selected
      btn.classList.add('is-active');
      btn.setAttribute('aria-selected', 'true');

      const panel = document.querySelector(`[data-tab-panel="${target}"]`);
      if (panel) {
        panel.hidden = false;
        panel.setAttribute('aria-hidden', 'false');
      }
    });
  });
}

/* ── Smooth Scroll ──────────────────────────────────────────────────── */

function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener('click', (e) => {
      const href   = anchor.getAttribute('href');
      if (href === '#') return;
      const target = document.querySelector(href);
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
}

/* ── Arabic Paragraph Detection ─────────────────────────────────────── */
/* Tandai <p>/<li> di .entry-content yang berisi teks Arab dengan class
 * .arabic-paragraph (styling RTL + font lebih besar di _article.scss). */

function initArabicParagraphs() {
  const content = document.querySelector('.entry-content');
  if (!content) return;

  const arabicPattern = /[؀-ۿ]/;

  content.querySelectorAll('p, li').forEach((el) => {
    if (arabicPattern.test(el.textContent)) {
      el.classList.add('arabic-paragraph');
    }
  });
}

/* ── Copy Link (share buttons) ─────────────────────────────────────── */

function initCopyLink() {
  document.querySelectorAll('[data-jalaversity-copy-url]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const url = btn.dataset.jalaversityCopyUrl;
      if (!url || !navigator.clipboard) return;

      navigator.clipboard.writeText(url).then(() => {
        const original = btn.getAttribute('aria-label');
        btn.setAttribute('aria-label', 'Link disalin!');
        setTimeout(() => btn.setAttribute('aria-label', original), 2000);
      });
    });
  });
}
