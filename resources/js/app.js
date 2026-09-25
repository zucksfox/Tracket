import './bootstrap';

const shell = document.querySelector('.app-shell');
if (shell) {
    const narrow = window.matchMedia('(max-width: 900px)');
    const toggles = [...document.querySelectorAll('[data-nav-toggle]')];
    const backdrop = document.querySelector('[data-nav-backdrop]');
    const panel = document.querySelector('#nav-panel');
    let opener;
    const sync = () => {
        const open = narrow.matches ? shell.classList.contains('nav-open') : true;
        toggles.forEach(button => {
            button.setAttribute('aria-expanded', String(open));
            const label = narrow.matches ? (open ? 'Sembunyikan menu navigasi' : 'Buka menu navigasi') : 'Navigasi utama';
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);
        });
        backdrop.hidden = !(narrow.matches && shell.classList.contains('nav-open'));
        if (panel) {
            panel.inert = narrow.matches && !shell.classList.contains('nav-open');
            panel.setAttribute('aria-hidden', String(narrow.matches && !shell.classList.contains('nav-open')));
        }
    };
    const closeMobile = () => {
        shell.classList.remove('nav-open');
        sync();
        opener?.focus();
    };
    toggles.forEach(button => button.addEventListener('click', () => {
        if (narrow.matches) {
            const opening = !shell.classList.contains('nav-open');
            if (opening) opener = button;
            shell.classList.toggle('nav-open', opening);
            sync();
            if (opening) panel.querySelector('a')?.focus(); else opener?.focus();
        } else {
            sync();
        }
    }));
    backdrop.addEventListener('click', closeMobile);
    narrow.addEventListener('change', () => { shell.classList.remove('nav-open'); sync(); });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            if (shell.classList.contains('nav-open')) closeMobile();
            document.querySelectorAll('details[open]').forEach(details => {
                details.open = false;
                details.querySelector('summary')?.focus();
            });
        }
        if (event.key === 'Tab' && narrow.matches && shell.classList.contains('nav-open')) {
            const items = [...document.querySelector('.app-sidebar').querySelectorAll('a,button')].filter(el => el.getClientRects().length);
            const first = items[0], last = items.at(-1);
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    document.addEventListener('click', event => {
        document.querySelectorAll('.notification-menu[open]').forEach(details => {
            if (!details.contains(event.target)) details.open = false;
        });
    });
    sync();
    document.querySelector('.app-main')?.classList.add('motion-content');
    const motionShell = document.querySelector('.app-shell');
    if (motionShell?.dataset.statusMotion === '1') document.querySelector('.stamp')?.classList.add('motion-status');

    const stockToastRegion = document.querySelector('[data-stock-toast-region]');
    const stockBadge = document.querySelector('[data-notification-badge]');
    const stockList = document.querySelector('[data-notification-list]');
    const userId = document.querySelector('.app-shell')?.dataset.userId || 'guest';
    const stockStateKey = `tracket.stockNotifications.${userId}`;
    const stockState = (() => { try { return JSON.parse(localStorage.getItem(stockStateKey) || '{}'); } catch { return {}; } })();
    const chartMotionKey = `tracket.chartMotion.${userId}.${location.pathname}`;
    const saveStockState = () => { try { localStorage.setItem(stockStateKey, JSON.stringify(stockState)); } catch {} };
    const renderStockBadge = () => {
        const unread = Object.values(stockState).filter(item => item.unread).length;
        if (stockBadge) { stockBadge.textContent = String(unread); stockBadge.hidden = unread === 0; }
    };
    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const dismissToast = toast => { toast.classList.add('motion-toast-out'); setTimeout(() => toast.remove(), 190); };
    const showStockToast = item => {
        if (!stockToastRegion) return;
        const toast = document.createElement('div');
        toast.className = 'stock-toast motion-toast-in';
        const title = item.stock === 0 ? 'Stok Habis' : 'Stok Menipis';
        const detail = item.stock === 0 ? escapeHtml(item.name) : `${escapeHtml(item.name)}, sisa ${item.stock} pcs`;
        toast.innerHTML = `<span class="stock-toast-icon">!</span><div><strong>${title}</strong><span>${detail}</span></div><button type="button" aria-label="Tutup notifikasi">&times;</button>`;
        toast.querySelector('button').addEventListener('click', () => dismissToast(toast));
        stockToastRegion.append(toast);
        setTimeout(() => dismissToast(toast), 6500);
    };
    const pollStock = async () => {
        try {
            const response = await fetch('/api/stock-notifications', { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!response.ok) return;
            const { parts = [] } = await response.json();
            const currentIds = new Set(parts.map(item => String(item.id)));
            parts.forEach(item => {
                const key = String(item.id);
                const previous = stockState[key];
                const changed = previous && (previous.stock !== item.stock || previous.updated_at !== item.updated_at);
                // Snapshot pertama hanya menjadi baseline; notifikasi hanya untuk perubahan berikutnya.
                if (previous && changed) { stockState[key] = { stock: item.stock, updated_at: item.updated_at, unread: true, name: item.name }; saveStockState(); showStockToast(item); stockBadge?.classList.remove('motion-badge'); void stockBadge?.offsetWidth; stockBadge?.classList.add('motion-badge'); }
                else if (!previous) stockState[key] = { stock: item.stock, updated_at: item.updated_at, unread: false, name: item.name };
            });
            Object.keys(stockState).forEach(key => { if (!currentIds.has(key)) delete stockState[key]; });
            if (stockList) stockList.innerHTML = parts.length ? parts.map(item => `<a href="/spareparts?filter=critical">${item.stock === 0 ? 'Stok Habis' : 'Stok Menipis'}: ${escapeHtml(item.name)}${item.stock === 0 ? '' : `, sisa ${item.stock} pcs`}</a>`).join('') : '<p>Tidak ada stok kritis.</p>';
            saveStockState(); renderStockBadge();
        } catch {}
    };
    document.querySelector('.notification-menu')?.addEventListener('toggle', event => { if (event.target.open) { Object.values(stockState).forEach(item => { item.unread = false; }); saveStockState(); renderStockBadge(); } });
    if (document.querySelector('.notification-menu')) { pollStock(); window.setInterval(pollStock, 15000); }
    const chart = document.querySelector('.dash-chart');
    if (chart) {
        let animated = false;
        try { animated = localStorage.getItem(chartMotionKey) === '1'; } catch {}
        if (!animated) {
            chart.classList.add('motion-chart');
            try { localStorage.setItem(chartMotionKey, '1'); } catch {}
        }
    }
    const errorFields = (() => { try { return JSON.parse(motionShell?.dataset.errorFields || '[]'); } catch { return []; } })();
    errorFields.forEach(name => document.querySelector(`[name="${CSS.escape(name)}"]`)?.classList.add('motion-error'));
    renderStockBadge();

    const account = document.querySelector('.account-menu');
    const accountToggle = document.querySelector('[data-account-toggle]');
    const accountPanel = document.querySelector('#account-panel');
    const logoutDialog = document.querySelector('#logout-dialog');
    const logoutForm = document.querySelector('#logout-form');
    const setAccount = open => {
        if (!account || account.classList.contains('is-open') === open && !accountPanel.classList.contains('is-closing')) return;
        account.classList.toggle('is-open', open);
        accountToggle.setAttribute('aria-expanded', String(open));
        accountPanel.hidden = false;
        if (open) {
            accountPanel.classList.remove('is-closing');
            requestAnimationFrame(() => accountPanel.classList.add('is-open'));
        } else {
            accountPanel.classList.remove('is-open');
            accountPanel.classList.add('is-closing');
            const hide = () => { if (!account.classList.contains('is-open')) { accountPanel.hidden = true; accountPanel.classList.remove('is-closing'); } };
            accountPanel.addEventListener('transitionend', hide, { once: true });
            setTimeout(hide, 220);
        }
    };
    accountToggle?.addEventListener('click', event => {
        event.stopPropagation();
        document.querySelectorAll('.notification-menu[open]').forEach(details => { details.open = false; });
        setAccount(!account.classList.contains('is-open'));
    });
    document.addEventListener('click', event => { if (account && !account.contains(event.target)) setAccount(false); });
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape' || !account?.classList.contains('is-open')) return;
        setAccount(false);
        accountToggle.focus();
    });
    document.querySelector('[data-logout-open]')?.addEventListener('click', () => {
        setAccount(false);
        logoutDialog.showModal();
    });
    document.querySelector('[data-logout-confirm]')?.addEventListener('click', () => logoutForm.submit());
}

// A submitter's name/value must stay enabled so status transitions retain their payload.
document.addEventListener('submit', event => {
    if (event.defaultPrevented || event.target.target === '_blank') return;
    const button = event.submitter;
    if (!button || button.dataset.noBusy !== undefined) return;
    if (event.target.dataset.submitting === 'true') { event.preventDefault(); return; }
    event.target.dataset.submitting = 'true';
    button.setAttribute('aria-busy', 'true');
    button.classList.add('is-busy');
    button.dataset.originalText = button.textContent;
    button.dataset.actionLabel = button.dataset.actionLabel || 'Menyimpan...';
    button.textContent = button.dataset.actionLabel;
});
window.addEventListener('pageshow', () => {
    document.querySelectorAll('[data-submitting]').forEach(form => delete form.dataset.submitting);
    document.querySelectorAll('[data-original-text]').forEach(button => {
        button.textContent = button.dataset.originalText;
        button.removeAttribute('aria-busy');
        button.classList.remove('is-busy');
        delete button.dataset.originalText;
    });
});
