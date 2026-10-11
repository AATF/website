/*!
 * Color mode toggler using Bootstrap's built-in CSS and data-bs-theme
 * (https://getbootstrap.com/docs/5.3/customize/color-modes/)
 */
(() => {
  'use strict';

  const STORAGE_KEY = 'theme';

  const getStoredTheme = () => {
    try {
      const stored = localStorage.getItem(STORAGE_KEY) || localStorage.getItem('aatf_theme');
      if (stored === 'light' || stored === 'dark' || stored === 'auto') {
        return stored;
      }
    } catch (e) {}

    try {
      const match = document.cookie.match(/(?:^|;\s*)(?:theme|aatf_theme)=([^;]*)/);
      if (match && (match[1] === 'light' || match[1] === 'dark' || match[1] === 'auto')) {
        return match[1];
      }
    } catch (e) {}

    return 'auto';
  };

  const setStoredTheme = theme => {
    try {
      localStorage.setItem(STORAGE_KEY, theme);
      localStorage.setItem('aatf_theme', theme);
      document.cookie = 'theme=' + encodeURIComponent(theme) + ';path=/;max-age=31536000;SameSite=Lax';
      document.cookie = 'aatf_theme=' + encodeURIComponent(theme) + ';path=/;max-age=31536000;SameSite=Lax';
    } catch (e) {}
  };

  const getPreferredTheme = () => {
    const storedTheme = getStoredTheme();
    if (storedTheme === 'light' || storedTheme === 'dark') {
      return storedTheme;
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  };

  const setTheme = theme => {
    const resolvedTheme = theme === 'auto'
      ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
      : theme;

    document.documentElement.setAttribute('data-bs-theme', resolvedTheme);
    document.documentElement.setAttribute('data-bs-theme-preference', theme);
  };

  setTheme(getStoredTheme());

  const showActiveTheme = (theme, focus = false) => {
    const themeSwitcher = document.querySelector('#bd-theme');
    const storedPreference = getStoredTheme();

    document.querySelectorAll('[data-bs-theme-value]').forEach(element => {
      const val = element.getAttribute('data-bs-theme-value');
      const isActive = val === storedPreference;
      element.classList.toggle('active', isActive);
      element.setAttribute('aria-pressed', isActive ? 'true' : 'false');

      const check = element.querySelector('.check-icon');
      if (check) {
        check.classList.toggle('d-none', !isActive);
      }
    });

    if (themeSwitcher) {
      const btnToActive = document.querySelector(`[data-bs-theme-value="${storedPreference}"]`);
      const activeThemeIconContainer = themeSwitcher.querySelector('.theme-icon-active');

      if (btnToActive && activeThemeIconContainer) {
        const iconSvg = btnToActive.querySelector('svg:not(.check-icon)');
        if (iconSvg) {
          activeThemeIconContainer.innerHTML = iconSvg.outerHTML;
          const currentSvg = activeThemeIconContainer.querySelector('svg');
          if (currentSvg) {
            currentSvg.classList.remove('me-2', 'mr-2', 'opacity-50');
          }
        }
      }

      const label = `Toggle theme (${storedPreference})`;
      themeSwitcher.setAttribute('aria-label', label);

      if (focus) {
        themeSwitcher.focus();
      }
    }

    // Update footer buttons if present
    document.querySelectorAll('.theme-footer-btn').forEach(btn => {
      const val = btn.getAttribute('data-bs-theme-value');
      const isActive = val === storedPreference;
      btn.classList.toggle('fw-bold', isActive);
      btn.classList.toggle('active', isActive);
      btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
  };

  // Listen for OS system theme changes
  if (window.matchMedia) {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    const handleSystemThemeChange = () => {
      const storedTheme = getStoredTheme();
      if (storedTheme === 'auto') {
        setTheme('auto');
        showActiveTheme('auto');
      }
    };

    if (mediaQuery.addEventListener) {
      mediaQuery.addEventListener('change', handleSystemThemeChange);
    } else if (mediaQuery.addListener) {
      mediaQuery.addListener(handleSystemThemeChange);
    }
  }

  // Cross-tab theme synchronization
  window.addEventListener('storage', e => {
    if (e.key === STORAGE_KEY || e.key === 'aatf_theme') {
      const newTheme = e.newValue || 'auto';
      setTheme(newTheme);
      showActiveTheme(newTheme);
    }
  });

  const initThemeSwitcher = () => {
    showActiveTheme(getStoredTheme());

    document.addEventListener('click', e => {
      const toggle = e.target.closest('[data-bs-theme-value]');
      if (toggle) {
        e.preventDefault();
        const theme = toggle.getAttribute('data-bs-theme-value');
        setStoredTheme(theme);
        setTheme(theme);
        showActiveTheme(theme, true);
      }
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initThemeSwitcher);
  } else {
    initThemeSwitcher();
  }

  // Expose global API
  window.AATFTheme = {
    getPreference: getStoredTheme,
    setPreference: theme => {
      setStoredTheme(theme);
      setTheme(theme);
      showActiveTheme(theme);
    },
    getResolvedTheme: () => document.documentElement.getAttribute('data-bs-theme') || getPreferredTheme()
  };
})();
