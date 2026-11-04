(() => {
  const root = document.documentElement;
  const themeKey = 'boardershub-theme';

  const readStoredTheme = () => {
    try {
      const theme = window.localStorage.getItem(themeKey);
      return theme === 'light' || theme === 'dark' ? theme : null;
    } catch (error) {
      if (error.name !== 'SecurityError') throw error;
      console.warn('Theme preference could not be read from local storage.');
      return null;
    }
  };

  let storedTheme = readStoredTheme();
  let currentTheme = storedTheme || 'dark';

  const updateControls = () => {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
      button.dataset.theme = currentTheme;
      button.setAttribute('aria-label', `Switch to ${currentTheme === 'dark' ? 'light' : 'dark'} mode`);
      button.title = `Switch to ${currentTheme === 'dark' ? 'light' : 'dark'} mode`;
      const label = button.querySelector('[data-theme-label]');
      if (label) label.textContent = currentTheme === 'dark' ? 'Light' : 'Dark';
    });

    const themeColor = document.querySelector('meta[name="theme-color"]');
    if (themeColor) themeColor.content = currentTheme === 'dark' ? '#080808' : '#ffffff';
  };

  const applyTheme = (theme, persist) => {
    currentTheme = theme;
    if (persist) {
      root.dataset.theme = theme;
      root.style.colorScheme = theme;
      storedTheme = theme;
      try {
        window.localStorage.setItem(themeKey, theme);
      } catch (error) {
        if (error.name !== 'SecurityError' && error.name !== 'QuotaExceededError') throw error;
        console.warn('Theme preference could not be saved; it will apply for this page only.');
      }
    } else {
      delete root.dataset.theme;
      root.style.removeProperty('color-scheme');
    }
    updateControls();
  };

  document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-theme-toggle]')) return;
    applyTheme(currentTheme === 'dark' ? 'light' : 'dark', true);
  });

  root.dataset.theme = currentTheme;
  root.style.colorScheme = currentTheme;
  updateControls();
})();
