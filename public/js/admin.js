/**
 * Commerce Admin — UI interactions
 */
(function () {
  'use strict';

  const Admin = {
    init() {
      this.initSidebar();
      this.initMobileMenu();
      this.initDropdowns();
      this.initCheckAll();
      this.initFlashToasts();
      this.initConfirmModals();
      this.initImagePreview();
      this.initFormLoading();
      this.initFilterDropdowns();
      this.initCharts();
      this.initAlertDismiss();
      this.initNotifications();
    },

    /* ------------------------------------------------------------------ */
    /* Sidebar                                                            */
    /* ------------------------------------------------------------------ */
    initSidebar() {
      const shell = document.querySelector('.admin-shell');
      const collapseBtn = document.querySelector('[data-sidebar-collapse]');
      const storageKey = 'admin.sidebar.collapsed';

      const MOBILE_BP = 768;

      if (shell && localStorage.getItem(storageKey) === '1' && window.innerWidth > MOBILE_BP) {
        shell.classList.add('sidebar-collapsed');
      }

      if (collapseBtn && shell) {
        collapseBtn.addEventListener('click', () => {
          if (window.innerWidth <= MOBILE_BP) return;
          shell.classList.toggle('sidebar-collapsed');
          localStorage.setItem(
            storageKey,
            shell.classList.contains('sidebar-collapsed') ? '1' : '0'
          );
        });
      }

      document.querySelectorAll('[data-nav-toggle]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          const item = btn.closest('.nav-item');
          if (!item) return;

          const parent = item.parentElement;
          if (parent) {
            parent.querySelectorAll('.nav-item.open').forEach((openItem) => {
              if (openItem !== item) openItem.classList.remove('open');
            });
          }
          item.classList.toggle('open');
        });
      });

      // Auto-open active submenu
      document.querySelectorAll('.nav-sublink.active').forEach((link) => {
        const item = link.closest('.nav-item');
        if (item) item.classList.add('open');
      });
    },

    /* ------------------------------------------------------------------ */
    /* Mobile menu                                                        */
    /* ------------------------------------------------------------------ */
    initMobileMenu() {
      const shell = document.querySelector('.admin-shell');
      const toggle = document.querySelector('[data-mobile-menu]');
      const overlay = document.querySelector('[data-sidebar-overlay]');

      const close = () => {
        if (!shell) return;
        shell.classList.remove('sidebar-mobile-open');
        if (overlay) overlay.classList.remove('visible');
        document.body.style.overflow = '';
      };

      const open = () => {
        if (!shell) return;
        shell.classList.add('sidebar-mobile-open');
        if (overlay) overlay.classList.add('visible');
        document.body.style.overflow = 'hidden';
      };

      if (toggle) {
        toggle.addEventListener('click', () => {
          if (shell && shell.classList.contains('sidebar-mobile-open')) close();
          else open();
        });
      }

      if (overlay) overlay.addEventListener('click', close);

      if (window.innerWidth > 768) close();

      window.addEventListener('resize', () => {
        if (window.innerWidth > 768) close();
      });

      document.querySelectorAll('.sidebar .nav-link[href], .sidebar .nav-sublink[href]').forEach((link) => {
        link.addEventListener('click', () => {
          if (window.innerWidth <= 768 && link.getAttribute('href') && link.getAttribute('href') !== '#') {
            close();
          }
        });
      });
    },

    /* ------------------------------------------------------------------ */
    /* Dropdowns (user / notifications)                                   */
    /* ------------------------------------------------------------------ */
    initDropdowns() {
      document.querySelectorAll('[data-dropdown]').forEach((wrap) => {
        const trigger = wrap.querySelector('[data-dropdown-trigger]');
        const menu = wrap.querySelector('[data-dropdown-menu]');
        if (!trigger || !menu) return;

        trigger.addEventListener('click', (e) => {
          e.stopPropagation();
          document.querySelectorAll('[data-dropdown-menu].open').forEach((m) => {
            if (m !== menu) m.classList.remove('open');
          });
          menu.classList.toggle('open');
        });
        menu.addEventListener('click', (e) => e.stopPropagation());
      });

      document.addEventListener('click', () => {
        document.querySelectorAll('[data-dropdown-menu].open').forEach((m) => m.classList.remove('open'));
        document.querySelectorAll('.filter-dropdown.open').forEach((d) => d.classList.remove('open'));
      });
    },

    /* ------------------------------------------------------------------ */
    /* Check-all                                                          */
    /* ------------------------------------------------------------------ */
    initCheckAll() {
      document.querySelectorAll('[data-check-all]').forEach((master) => {
        master.addEventListener('change', () => {
          const table = master.closest('table') || master.closest('form') || document;
          const boxes = table.querySelectorAll('tbody input[type="checkbox"][name="ids[]"], tbody input[type="checkbox"][data-check-item]');
          boxes.forEach((box) => {
            box.checked = master.checked;
          });
          this.updateBulkCount(table);
        });
      });

      document.querySelectorAll('tbody input[type="checkbox"][name="ids[]"], tbody input[type="checkbox"][data-check-item]').forEach((box) => {
        box.addEventListener('change', () => {
          const table = box.closest('table');
          if (!table) return;
          const master = table.querySelector('[data-check-all]');
          const items = table.querySelectorAll('tbody input[type="checkbox"][name="ids[]"], tbody input[type="checkbox"][data-check-item]');
          if (master && items.length) {
            master.checked = Array.from(items).every((i) => i.checked);
            master.indeterminate = !master.checked && Array.from(items).some((i) => i.checked);
          }
          this.updateBulkCount(table);
        });
      });
    },

    updateBulkCount(scope) {
      const countEl = (scope.closest('form') || document).querySelector('[data-bulk-count]');
      if (!countEl) return;
      const checked = (scope.closest('form') || scope).querySelectorAll(
        'tbody input[type="checkbox"][name="ids[]"]:checked, tbody input[type="checkbox"][data-check-item]:checked'
      ).length;
      countEl.textContent = checked ? `${checked} selected` : 'None selected';
    },

    /* ------------------------------------------------------------------ */
    /* Flash → toasts                                                     */
    /* ------------------------------------------------------------------ */
    initFlashToasts() {
      if (window.AppAlert) return;

      const flash = document.getElementById('admin-flash-data');
      if (!flash) return;

      try {
        const data = JSON.parse(flash.textContent || '{}');
        if (data.success) this.toast(data.success, 'success', 'Success');
        if (data.error) this.toast(data.error, 'error', 'Error');
        if (data.warning) this.toast(data.warning, 'warning', 'Warning');
        if (data.info) this.toast(data.info, 'info', 'Info');
        if (data.status) this.toast(data.status, 'success', 'Success');
        if (Array.isArray(data.errors)) {
          data.errors.forEach((msg) => this.toast(msg, 'error', 'Validation'));
        }
      } catch (e) {
        // ignore malformed flash payload
      }
    },

    toast(message, type = 'info', title = '') {
      let container = document.querySelector('.toast-container');
      if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
      }

      const el = document.createElement('div');
      el.className = `toast toast-${type}`;
      el.innerHTML = `
        <div class="toast-body">
          ${title ? `<div class="toast-title">${this.escape(title)}</div>` : ''}
          <div>${this.escape(String(message))}</div>
        </div>
        <button type="button" class="toast-close" aria-label="Close">&times;</button>
      `;

      const remove = () => {
        el.style.opacity = '0';
        el.style.transform = 'translateX(12px)';
        el.style.transition = 'all 180ms ease';
        setTimeout(() => el.remove(), 180);
      };

      el.querySelector('.toast-close').addEventListener('click', remove);
      container.appendChild(el);
      setTimeout(remove, 5200);
    },

    escape(str) {
      const d = document.createElement('div');
      d.textContent = str;
      return d.innerHTML;
    },

    /* ------------------------------------------------------------------ */
    /* Confirm modal helper                                               */
    /* ------------------------------------------------------------------ */
    initConfirmModals() {
      if (window.AppAlert) return;

      const backdrop = document.getElementById('confirm-modal');
      if (!backdrop) return;

      const titleEl = backdrop.querySelector('[data-confirm-title]');
      const messageEl = backdrop.querySelector('[data-confirm-message]');
      const confirmBtn = backdrop.querySelector('[data-confirm-ok]');
      const cancelBtns = backdrop.querySelectorAll('[data-confirm-cancel]');

      let pendingAction = null;

      const close = () => {
        backdrop.classList.remove('open');
        pendingAction = null;
      };

      cancelBtns.forEach((btn) => btn.addEventListener('click', close));
      backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) close();
      });

      if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
          const action = pendingAction;
          close();
          if (!action) return;
          if (typeof action === 'function') action();
          else if (action instanceof HTMLFormElement) action.submit();
          else if (typeof action === 'string') window.location.href = action;
        });
      }

      window.AdminConfirm = (options = {}) => {
        if (titleEl) titleEl.textContent = options.title || 'Are you sure?';
        if (messageEl) messageEl.textContent = options.message || 'This action cannot be undone.';
        if (confirmBtn) {
          confirmBtn.textContent = options.confirmText || 'Confirm';
          confirmBtn.className = 'btn ' + (options.danger ? 'btn-danger' : 'btn-primary');
        }
        pendingAction = options.onConfirm || options.form || options.href || null;
        backdrop.classList.add('open');
      };

      document.querySelectorAll('[data-confirm]').forEach((el) => {
        el.addEventListener('click', (e) => {
          e.preventDefault();
          const message = el.getAttribute('data-confirm') || 'Are you sure?';
          const title = el.getAttribute('data-confirm-title') || 'Confirm';
          const formId = el.getAttribute('data-confirm-form');
          const href = el.getAttribute('href');

          window.AdminConfirm({
            title,
            message,
            danger: el.classList.contains('btn-danger') || el.hasAttribute('data-confirm-danger'),
            onConfirm: () => {
              if (formId) {
                const form = document.getElementById(formId);
                if (form) form.submit();
              } else if (el.tagName === 'BUTTON' && el.form) {
                el.form.submit();
              } else if (href && href !== '#') {
                window.location.href = href;
              }
            },
          });
        });
      });
    },

    /* ------------------------------------------------------------------ */
    /* Image upload preview                                               */
    /* ------------------------------------------------------------------ */
    initImagePreview() {
      document.querySelectorAll('input[type="file"][accept*="image"], input[type="file"][data-preview]').forEach((input) => {
        input.addEventListener('change', () => {
          const file = input.files && input.files[0];
          let preview = input.parentElement.querySelector('.image-preview');

          if (!preview) {
            preview = document.createElement('div');
            preview.className = 'image-preview';
            preview.innerHTML = '<img alt="Preview">';
            input.parentElement.appendChild(preview);
          }

          const img = preview.querySelector('img');
          if (!file || !file.type.startsWith('image/')) {
            preview.classList.remove('visible');
            if (img) img.removeAttribute('src');
            return;
          }

          const reader = new FileReader();
          reader.onload = (e) => {
            if (img) img.src = e.target.result;
            preview.classList.add('visible');
          };
          reader.readAsDataURL(file);
        });
      });
    },

    /* ------------------------------------------------------------------ */
    /* Form submit loading                                                */
    /* ------------------------------------------------------------------ */
    initFormLoading() {
      document.querySelectorAll('form:not([data-no-loading])').forEach((form) => {
        form.addEventListener('submit', () => {
          if (form.dataset.loading === '1') return;
          form.dataset.loading = '1';

          const submitters = form.querySelectorAll('button[type="submit"], input[type="submit"], .btn-primary');
          submitters.forEach((btn) => {
            btn.classList.add('is-loading');
            btn.disabled = true;
            if (btn.tagName === 'BUTTON' && !btn.querySelector('.spinner')) {
              const spin = document.createElement('span');
              spin.className = 'spinner spinner-sm';
              btn.prepend(spin);
            }
          });

          const pageLoader = document.querySelector('.page-loader');
          if (pageLoader) pageLoader.classList.add('active');
        });
      });
    },

    /* ------------------------------------------------------------------ */
    /* Dropdown filters                                                   */
    /* ------------------------------------------------------------------ */
    initFilterDropdowns() {
      document.querySelectorAll('.filter-dropdown').forEach((wrap) => {
        const btn = wrap.querySelector('.filter-dropdown-btn, [data-filter-toggle]');
        if (!btn) return;
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          document.querySelectorAll('.filter-dropdown.open').forEach((d) => {
            if (d !== wrap) d.classList.remove('open');
          });
          wrap.classList.toggle('open');
        });
        const panel = wrap.querySelector('.filter-dropdown-panel');
        if (panel) panel.addEventListener('click', (e) => e.stopPropagation());
      });
    },

    /* ------------------------------------------------------------------ */
    /* Chart.js helper                                                    */
    /* ------------------------------------------------------------------ */
    initCharts() {
      if (typeof window.Chart === 'undefined') return;

      document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        if (canvas._adminChart) return;

        let labels = [];
        let values = [];
        let type = canvas.getAttribute('data-chart') || 'line';

        try {
          labels = JSON.parse(canvas.getAttribute('data-labels') || '[]');
          values = JSON.parse(canvas.getAttribute('data-values') || '[]');
        } catch (e) {
          return;
        }

        const label = canvas.getAttribute('data-label') || 'Data';
        const color = canvas.getAttribute('data-color') || '#0d9488';
        const fill = canvas.getAttribute('data-fill') !== 'false';

        canvas._adminChart = new window.Chart(canvas.getContext('2d'), {
          type,
          data: {
            labels,
            datasets: [
              {
                label,
                data: values,
                borderColor: color,
                backgroundColor: fill
                  ? this.hexToRgba(color, type === 'line' ? 0.12 : 0.75)
                  : color,
                borderWidth: type === 'line' ? 2.5 : 1,
                tension: 0.35,
                fill: type === 'line' ? fill : true,
                pointRadius: type === 'line' ? 3 : 0,
                pointHoverRadius: 5,
                borderRadius: type === 'bar' ? 6 : 0,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: canvas.getAttribute('data-legend') === 'true',
                labels: { boxWidth: 12, font: { family: "'DM Sans', sans-serif" } },
              },
              tooltip: {
                backgroundColor: '#0f172a',
                padding: 10,
                cornerRadius: 8,
                titleFont: { family: "'DM Sans', sans-serif", weight: '600' },
                bodyFont: { family: "'DM Sans', sans-serif" },
              },
            },
            scales:
              type === 'doughnut' || type === 'pie'
                ? {}
                : {
                    x: {
                      grid: { display: false },
                      ticks: { color: '#64748b', font: { size: 11 } },
                    },
                    y: {
                      beginAtZero: true,
                      grid: { color: 'rgba(226, 232, 240, 0.9)' },
                      ticks: { color: '#64748b', font: { size: 11 } },
                      border: { display: false },
                    },
                  },
          },
        });
      });
    },

    hexToRgba(hex, alpha) {
      const cleaned = hex.replace('#', '');
      const full =
        cleaned.length === 3
          ? cleaned
              .split('')
              .map((c) => c + c)
              .join('')
          : cleaned;
      const num = parseInt(full, 16);
      const r = (num >> 16) & 255;
      const g = (num >> 8) & 255;
      const b = num & 255;
      return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    },

    /* ------------------------------------------------------------------ */
    /* Alert dismiss                                                      */
    /* ------------------------------------------------------------------ */
    initAlertDismiss() {
      document.querySelectorAll('.alert-close').forEach((btn) => {
        btn.addEventListener('click', () => {
          const alert = btn.closest('.alert');
          if (alert) alert.remove();
        });
      });
    },

    /* ------------------------------------------------------------------ */
    /* Order / return alerts                                              */
    /* ------------------------------------------------------------------ */
    initNotifications() {
      const source = document.getElementById('admin-notify-data');
      if (!source) return;

      let config = {};
      try {
        config = JSON.parse(source.textContent || '{}');
      } catch (err) {
        return;
      }

      const csrf = config.csrf || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const seenKey = 'gj.admin.alert.ids';
      const seen = new Set((sessionStorage.getItem(seenKey) || '').split(',').filter(Boolean));

      const saveSeen = () => {
        const ids = Array.from(seen).slice(-80);
        sessionStorage.setItem(seenKey, ids.join(','));
      };

      const setBadge = (count) => {
        const badge = document.getElementById('admin-notify-badge');
        if (!badge) return;
        const n = Number(count) || 0;
        badge.hidden = n < 1;
        badge.textContent = n > 9 ? '9+' : String(n);
      };

      const iconSvg = (type) => {
        if (type === 'return_requested') {
          return '<svg viewBox="0 0 24 24"><path d="M9 14 4 9l5-5"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>';
        }
        if (type === 'order_created') {
          return '<svg viewBox="0 0 24 24"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 5 3H2"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>';
        }
        return '<svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>';
      };

      const renderList = (items) => {
        const list = document.getElementById('admin-notify-list');
        if (!list) return;
        if (!items || !items.length) {
          list.innerHTML = '<p class="notify-panel__empty">No notifications yet. New orders and returns will appear here.</p>';
          return;
        }
        list.innerHTML = items
          .map((item) => {
        const kind = item.type === 'return_requested' ? 'return' : item.type === 'order_created' ? 'order' : (item.type === 'enquiry_created' || item.type === 'newsletter_subscribed' ? 'enquiry' : 'info');
            return `<form method="POST" action="${item.read_url}" data-no-loading>
              <input type="hidden" name="_token" value="${csrf}">
              <button type="submit" class="notify-item${item.read ? '' : ' is-unread'}">
                <span class="notify-item__icon notify-item__icon--${kind}">${iconSvg(item.type)}</span>
                <span class="notify-item__copy">
                  <strong></strong>
                  <small></small>
                  <em></em>
                </span>
              </button>
            </form>`;
          })
          .join('');
        Array.from(list.querySelectorAll('.notify-item')).forEach((btn, i) => {
          const item = items[i];
          if (!item) return;
          const strong = btn.querySelector('strong');
          const small = btn.querySelector('small');
          const em = btn.querySelector('em');
          if (strong) strong.textContent = item.title || '';
          if (small) small.textContent = item.message || '';
          if (em) em.textContent = item.time || '';
        });
      };

      const markSeen = (alerts) => {
        (alerts || []).forEach((item) => seen.add(String(item.id)));
        saveSeen();
      };

      const popup = (alerts) => {
        const fresh = (alerts || []).filter((item) => item && item.alert && !seen.has(String(item.id)));
        if (!fresh.length) return;
        markSeen(fresh);

        const show = (item) => {
          if (window.AppAlert && typeof window.AppAlert.notice === 'function') {
            window.AppAlert.notice(item);
            return;
          }
          if (typeof window.Swal === 'undefined') return;
          window.Swal.fire({
            toast: true,
            position: 'top-end',
            icon: item.type === 'return_requested' ? 'warning' : 'success',
            title: item.title,
            text: item.message,
            showConfirmButton: true,
            confirmButtonText: 'View',
            showCloseButton: true,
            timer: 9000,
            timerProgressBar: true,
          }).then((res) => {
            if (res.isConfirmed && item.read_url) {
              const form = document.createElement('form');
              form.method = 'POST';
              form.action = item.read_url;
              const token = document.createElement('input');
              token.type = 'hidden';
              token.name = '_token';
              token.value = csrf;
              form.appendChild(token);
              document.body.appendChild(form);
              form.submit();
            }
          });
        };

        if (fresh.length === 1) {
          show(fresh[0]);
          return;
        }

        const first = fresh[0];
        show({
          ...first,
          title: fresh.length + ' new alerts',
          message: fresh.map((row) => row.title).join(' · '),
        });
      };

      setBadge(config.count);
      popup(config.alerts || []);

      const poll = () => {
        if (!config.feed) return;
        fetch(config.feed, {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        })
          .then((res) => (res.ok ? res.json() : null))
          .then((data) => {
            if (!data) return;
            setBadge(data.count);
            if (Array.isArray(data.items)) renderList(data.items);
            popup(data.alerts || []);
          })
          .catch(() => {});
      };

      setInterval(poll, 20000);
      document.addEventListener('visibilitychange', () => {
        if (!document.hidden) poll();
      });
    },
  };

  document.addEventListener('DOMContentLoaded', () => Admin.init());
  window.AdminUI = Admin;
})();
