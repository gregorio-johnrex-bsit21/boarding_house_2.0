(() => {
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const main = document.querySelector('.portal-v2 main');
  const authCard = document.querySelector('.auth .acard');
  const mapFrame = document.querySelector('#bh-tenant-map');
  const mapWrapper = mapFrame?.closest('.map-frame');
  const mapLoading = mapWrapper?.querySelector('#map-loading');

  if (mapFrame && mapWrapper && mapLoading) {
    let mapRevealed = false;
    let mapFallbackTimer;

    const revealMap = (fallback = false) => {
      if (mapRevealed) return;
      mapRevealed = true;
      window.clearTimeout(mapFallbackTimer);

      const finishReveal = () => {
        mapWrapper.classList.add('is-loaded');
        mapLoading.classList.add('is-hidden');
        if (fallback || reducedMotion) {
          mapLoading.hidden = true;
        } else {
          window.setTimeout(() => { mapLoading.hidden = true; }, 350);
        }
      };

      if (fallback || reducedMotion) finishReveal();
      else window.setTimeout(finishReveal, 250);
    };

    mapFrame.addEventListener('load', () => revealMap(), { once: true });
    mapFallbackTimer = window.setTimeout(() => revealMap(true), 8000);

    try {
      if (mapFrame.contentDocument?.readyState === 'complete') revealMap();
    } catch (error) {
      if (error.name !== 'SecurityError') throw error;
    }
  }

  if (authCard && !reducedMotion) {
    authCard.style.animation = 'none';
    void authCard.offsetWidth;
    authCard.style.removeProperty('animation');
  }

  const shake = (card) => {
    if (!card) return;
    card.classList.remove('shake');
    void card.offsetWidth;
    card.classList.add('shake');
  };

  main?.querySelectorAll('[data-s]').forEach((element, index) => {
    element.style.setProperty('--i', index);
  });

  const toast = document.querySelector('#tt');
  let toastTimer;

  window.portalV2Toast = (message) => {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('on');
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => toast.classList.remove('on'), 2600);
  };

  if (toast?.textContent.trim()) {
    toast.classList.add('on');
    toastTimer = window.setTimeout(() => toast.classList.remove('on'), 2600);
  }

  let previouslyFocused;
  let overlayOpener;
  const overlay = document.querySelector('#ovl');
  const overlayFocusables = () => overlay
    ? [...overlay.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')]
      .filter((element) => !element.closest('[hidden]') && element.getAttribute('aria-hidden') !== 'true')
    : [];
  const closeOverlay = () => {
    if (!overlay || overlay.hidden) return;
    overlay.hidden = true;
    (overlayOpener?.isConnected ? overlayOpener : previouslyFocused)?.focus();
  };

  document.addEventListener('click', (event) => {
    const openButton = event.target.closest('[data-v2-open]');
    if (openButton && overlay) {
      previouslyFocused = document.activeElement;
      overlayOpener = openButton;
      const title = overlay.querySelector('#st');
      if (title) title.textContent = openButton.dataset.v2Title || '';
      overlay.hidden = false;
      overlay.querySelector('input:not([type="hidden"]), select, textarea, button')?.focus();
      return;
    }

    if (!overlay || overlay.hidden) return;
    if (event.target.closest('[data-v2-close]') || event.target === overlay) {
      closeOverlay();
    }
  });

  document.addEventListener('click', (event) => {
    const choice = event.target.closest('[data-device-choice]');
    if (!choice || !overlay || overlay.hidden) return;
    const input = overlay.querySelector('#device-name');
    if (!input) return;
    input.value = choice.dataset.deviceChoice;
    input.focus();
  });

  const syncCatalogDeviceChoice = (form) => {
    const selected = form?.querySelector('input[name="catalog_device_id"]:checked');
    const otherField = form?.querySelector('[data-other-device-field]');
    const nameInput = otherField?.querySelector('#device-name');
    if (!otherField || !nameInput) return;

    const isOther = !selected || selected.value === '';
    otherField.hidden = !isOther;
    nameInput.required = isOther;
    nameInput.disabled = !isOther;
  };

  document.querySelectorAll('#ovl form').forEach(syncCatalogDeviceChoice);
  document.addEventListener('change', (event) => {
    if (event.target.matches('input[name="catalog_device_id"]')) {
      syncCatalogDeviceChoice(event.target.closest('form'));
    }
  });

  document.addEventListener('click', (event) => {
    const catalogChoice = event.target.closest('[data-catalog-device-choice], [data-catalog-other]');
    if (!catalogChoice || !overlay) return;

    const value = catalogChoice.hasAttribute('data-catalog-other')
      ? ''
      : catalogChoice.dataset.catalogDeviceChoice;
    const option = [...overlay.querySelectorAll('input[name="catalog_device_id"]')]
      .find((input) => input.value === value && !input.disabled);
    if (!option) return;
    option.checked = true;
    option.dispatchEvent(new Event('change', { bubbles: true }));
    if (value === '') {
      overlay.querySelector('#device-name')?.focus();
    } else {
      option.focus();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (!overlay || overlay.hidden) return;
    if (event.key === 'Escape') {
      closeOverlay();
      return;
    }
    if (event.key !== 'Tab') return;

    const focusables = overlayFocusables();
    if (!focusables.length) {
      event.preventDefault();
      overlay.querySelector('[role="dialog"]')?.focus();
      return;
    }
    const first = focusables[0];
    const last = focusables[focusables.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });

  document.addEventListener('click', (event) => {
    const methodButton = event.target.closest('[data-payment-method]');
    if (!methodButton) return;

    const form = methodButton.closest('form');
    const methodInput = form?.querySelector('#payment-method');
    const referenceInput = form?.querySelector('#pay-reference');
    if (!methodInput || !referenceInput) return;

    methodInput.value = methodButton.dataset.paymentMethod;
    referenceInput.required = ['gcash', 'maya'].includes(methodInput.value);
    form.querySelectorAll('[data-payment-method]').forEach((button) => {
      button.setAttribute('aria-pressed', String(button === methodButton));
    });
  });

  document.querySelectorAll('.repair-file-native').forEach((input) => {
    input.addEventListener('change', () => {
      const filename = input.closest('.repair-file-control')?.querySelector('[data-file-name]');
      if (filename) filename.textContent = input.files?.[0]?.name || 'No file chosen';
    });
  });

  document.addEventListener('click', (event) => {
    const choice = event.target.closest('[data-repair-choice]');
    if (choice) {
      const form = choice.closest('form');
      const field = form?.querySelector(`#repair-${choice.dataset.repairChoice}`);
      if (!field) return;

      field.value = choice.dataset.value;
      form.querySelectorAll(`[data-repair-choice="${choice.dataset.repairChoice}"]`).forEach((button) => {
        button.setAttribute('aria-pressed', String(button === choice));
      });
      return;
    }

    const flip = event.target.closest('[data-id-card], [data-id-card-toggle]');
    if (flip) {
      const card = document.querySelector('[data-id-card]');
      if (!card) return;
      const isFlipped = card.classList.toggle('fl');
      card.setAttribute('aria-expanded', String(isFlipped));
    }
  });

  document.addEventListener('click', (event) => {
    const selector = event.target.closest('[data-repair-select]');
    if (selector) {
      const id = selector.dataset.repairSelect;
      document.querySelectorAll('[data-repair-panel]').forEach((panel) => {
        const selected = panel.dataset.repairPanel === id;
        panel.hidden = !selected;
        panel.classList.toggle('open', selected);
        panel.setAttribute('aria-expanded', String(selected));
        panel.querySelector('[data-repair-toggle]')?.setAttribute('aria-expanded', String(selected));
      });
      document.querySelectorAll('[data-repair-select]').forEach((item) => {
        item.dataset.cur = String(item === selector);
      });
      document.querySelector('[data-repair-feature]')?.scrollIntoView({
        behavior: reducedMotion ? 'auto' : 'smooth',
        block: 'nearest',
      });
      return;
    }

    const repairToggle = event.target.closest('[data-repair-toggle]');
    if (repairToggle) {
      const repairCard = repairToggle.closest('[data-repair-feature]');
      if (!repairCard) return;
      const expanded = repairCard.classList.toggle('open');
      repairCard.setAttribute('aria-expanded', String(expanded));
      repairToggle.setAttribute('aria-expanded', String(expanded));
      return;
    }

    const repairCard = event.target.closest('[data-repair-feature]');
    if (repairCard && !event.target.closest('a, button, input, select, textarea, summary')) {
      const expanded = repairCard.classList.toggle('open');
      repairCard.setAttribute('aria-expanded', String(expanded));
    }
  });

  document.addEventListener('keydown', (event) => {
    if ((event.key !== 'Enter' && event.key !== ' ') || !event.target.matches('[data-repair-select]')) return;
    event.preventDefault();
    event.target.click();
  });

  document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-persisted-switch]');
    if (!toggle) return;

    const value = document.getElementById(toggle.dataset.persistedSwitch);
    if (!value) return;
    const enabled = toggle.getAttribute('aria-checked') !== 'true';
    toggle.setAttribute('aria-checked', String(enabled));
    value.value = enabled ? '1' : '0';
    value.dispatchEvent(new Event('change', { bubbles: true }));

    if (toggle.dataset.persistedSwitch === 'share-contact-value') {
      const sharingStatus = document.querySelector('[data-id-sharing]');
      if (sharingStatus) {
        sharingStatus.textContent = enabled
          ? 'Shared with roommates after saving'
          : 'Private to you and your landlord after saving';
      }
    }
  });

  document.querySelectorAll('[data-id-preview-form] [data-id-preview]').forEach((input) => {
    input.addEventListener('input', () => {
      const name = document.querySelector('[data-id-preview="name"]')?.value.trim() || 'Your name';
      const email = document.querySelector('[data-id-preview="email"]')?.value.trim() || 'Email not set';
      const phone = document.querySelector('[data-id-preview="phone"]')?.value.trim() || 'Not set';
      const emergencyName = document.querySelector('[data-id-preview="emergency"]')?.value.trim();
      const emergencyPhone = document.querySelector('[data-id-preview="emergency-phone"]')?.value.trim();
      const initial = [...name][0]?.toLocaleUpperCase() || '?';
      const emergency = emergencyName || emergencyPhone
        ? `${emergencyName || '—'}${emergencyName && emergencyPhone ? ' · ' : ''}${emergencyPhone || ''}`
        : 'Not set';

      document.querySelector('[data-id-name]')?.replaceChildren(document.createTextNode(name));
      document.querySelector('[data-id-email]')?.replaceChildren(document.createTextNode(email));
      document.querySelector('[data-id-initial]')?.replaceChildren(document.createTextNode(initial));
      document.querySelector('[data-id-phone]')?.replaceChildren(document.createTextNode(phone));
      document.querySelector('[data-id-emergency]')?.replaceChildren(document.createTextNode(emergency));
    });
  });

  document.querySelectorAll('form[data-v2-password-strength]').forEach((form) => {
    const current = form.querySelector('[name="current_password"]');
    const password = form.querySelector('[name="password"]');
    const confirmation = form.querySelector('[name="password_confirmation"]');
    const submit = form.querySelector('[data-password-submit]');
    const strength = form.querySelector('[data-password-strength]');
    const rules = form.querySelector('[data-password-rules]');
    if (!current || !password || !confirmation || !submit || !strength || !rules) return;

    const updatePasswordHints = () => {
      const tests = [
        current.value.length > 0,
        password.value.length >= 8,
        /[A-Za-z]/.test(password.value) && /\d/.test(password.value),
        password.value.length > 0 && password.value === confirmation.value,
        password.value.length > 0 && password.value !== current.value,
      ];
      rules.querySelectorAll('[data-rule]').forEach((item, index) => {
        item.classList.toggle('ok', tests[index]);
      });

      let score = 0;
      if (password.value.length >= 8) score++;
      if (password.value.length >= 12) score++;
      if (/[a-z]/.test(password.value) && /[A-Z]/.test(password.value)) score++;
      if (/\d/.test(password.value) && /[^A-Za-z0-9]/.test(password.value)) score++;
      strength.querySelectorAll('i').forEach((item, index) => {
        item.className = index < score ? `p${score}` : '';
      });
      submit.disabled = !tests.every(Boolean);
    };

    [current, password, confirmation].forEach((input) => {
      input.addEventListener('input', updatePasswordHints);
    });
    updatePasswordHints();
  });

  document.querySelectorAll('form[data-v2-validate]').forEach((form) => {
    const amountInput = form.querySelector('[data-balance-limit]');
    if (amountInput) {
      const validateBalanceLimit = () => {
        const amount = Number(amountInput.value);
        const limit = Number(amountInput.dataset.balanceLimit);
        amountInput.setCustomValidity(
          amountInput.value && amount > limit
            ? `Amount cannot exceed the remaining balance of ₱${limit.toFixed(2)}.`
            : '',
        );
      };
      amountInput.addEventListener('input', validateBalanceLimit);
      validateBalanceLimit();
    }

    form.addEventListener('submit', (event) => {
      if (form.checkValidity()) return;
      event.preventDefault();
      form.reportValidity();
      shake(form.closest('.acard'));
    });
  });

  document.querySelectorAll('.acard').forEach((card) => {
    if ([...card.querySelectorAll('.er-t')].some((error) => error.textContent.trim())) {
      shake(card);
    }
  });

  document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-v2-password-toggle]');
    if (!toggle) return;

    const input = toggle.parentElement?.querySelector('input');
    if (!input) return;

    const visible = input.type === 'password';
    input.type = visible ? 'text' : 'password';
    toggle.textContent = visible ? 'Hide' : 'Show';
    toggle.setAttribute('aria-pressed', String(visible));
    toggle.setAttribute('aria-label', `${visible ? 'Hide' : 'Show'} ${input.labels?.[0]?.textContent.toLowerCase() || 'password'}`);
  });

  const formatMoney = (value) => Number(value || 0).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).replace(/[\u00a0\u202f ]/g, '');

  const rollOdometer = (element) => {
    const formatted = formatMoney(element.dataset.odo);
    const digits = [...formatted].map((character) => (
      /\d/.test(character)
        ? '<span class="dg"><i>0\n1\n2\n3\n4\n5\n6\n7\n8\n9</i></span>'
        : `<span>${character}</span>`
    )).join('');
    element.replaceChildren();
    const currency = document.createElement('span');
    currency.textContent = '₱';
    element.append(currency);
    element.insertAdjacentHTML('beforeend', digits);

    let digitIndex = 0;
    [...formatted].forEach((character) => {
      if (!/\d/.test(character)) return;
      const digit = element.querySelectorAll('.dg i')[digitIndex++];
      digit.style.transform = `translateY(-${Number(character) * 10}%)`;
    });
  };

  document.querySelectorAll('[data-odo]').forEach((element) => rollOdometer(element));

  document.querySelectorAll('[data-tank]').forEach((tank) => {
    const level = tank.querySelector('[data-tank-level]');
    if (!level) return;
    const ratio = Math.min(1, Math.max(0, Number(tank.dataset.tank) || 0));
    window.requestAnimationFrame(() => {
      level.style.transform = `translateY(${190 - (176 * ratio)}px)`;
    });
  });

  const timeline = document.querySelector('[data-timeline]');
  const tooltip = document.querySelector('#tp');
  let pinnedDot = null;

  if (timeline && tooltip) {
    const showTooltip = (dot, pointerEvent) => {
      tooltip.textContent = `${dot.dataset.d}: ${dot.dataset.w}`;
      const rect = dot.getBoundingClientRect();
      const left = pointerEvent ? pointerEvent.clientX : rect.left;
      const top = pointerEvent ? pointerEvent.clientY : rect.top;
      tooltip.style.transform = `translate(${Math.max(8, Math.min(window.innerWidth - 248, left + 14))}px,${Math.min(window.innerHeight - 72, top + 14)}px)`;
      tooltip.style.opacity = '1';
    };

    timeline.addEventListener('pointerover', (event) => {
      const dot = event.target.closest('.dot');
      if (dot && !pinnedDot) showTooltip(dot, event);
    });
    timeline.addEventListener('pointermove', (event) => {
      const dot = event.target.closest('.dot');
      if (dot && !pinnedDot) showTooltip(dot, event);
    }, { passive: true });
    timeline.addEventListener('pointerout', (event) => {
      if (!pinnedDot && event.target.closest('.dot')) tooltip.style.opacity = '0';
    });
    timeline.addEventListener('focusin', (event) => {
      const dot = event.target.closest('.dot');
      if (dot) showTooltip(dot);
    });
    timeline.addEventListener('focusout', () => {
      tooltip.style.opacity = '0';
    });
    timeline.addEventListener('click', (event) => {
      const dot = event.target.closest('.dot');
      if (!dot) return;
      pinnedDot = pinnedDot === dot ? null : dot;
      if (pinnedDot) showTooltip(dot, event.detail ? event : null);
      else tooltip.style.opacity = '0';
    });
    document.addEventListener('click', (event) => {
      if (pinnedDot && !timeline.contains(event.target)) {
        pinnedDot = null;
        tooltip.style.opacity = '0';
      }
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && pinnedDot) {
        pinnedDot = null;
        tooltip.style.opacity = '0';
      }
    });
  }

  document.querySelectorAll('[data-profile-edit-form]').forEach((form) => {
    const indicator = document.querySelector('[data-profile-unsaved]');
    const discard = form.querySelector('[data-profile-discard]');
    if (!indicator || !discard) return;
    const originalValues = new Map([...form.elements].map((field) => [field, field.value]));
    const syncDirtyState = () => {
      const dirty = [...originalValues].some(([field, value]) => field.value !== value);
      indicator.hidden = !dirty;
      discard.hidden = !dirty;
    };
    form.addEventListener('input', syncDirtyState);
    form.addEventListener('change', syncDirtyState);
    discard.addEventListener('click', () => {
      form.reset();
      form.querySelectorAll('[data-persisted-switch]').forEach((toggle) => {
        const value = document.getElementById(toggle.dataset.persistedSwitch);
        if (value) toggle.setAttribute('aria-checked', String(value.value === '1'));
      });
      form.querySelectorAll('[data-id-preview]').forEach((field) => {
        field.dispatchEvent(new Event('input', { bubbles: true }));
      });
      syncDirtyState();
      window.portalV2Toast?.('Unsaved changes discarded.');
    });
    syncDirtyState();
  });
})();
