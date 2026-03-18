/**
 * CineWeeknd v2.0 — Main JavaScript
 */

const APP_URL = document.querySelector('meta[name="app-url"]')?.content || '';

// -------------------------------------------------------
// Navbar scroll effect
// -------------------------------------------------------
const navbar = document.getElementById('mainNav');
if (navbar) {
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 40);
    }, { passive: true });
}

// -------------------------------------------------------
// Cart badge updater
// -------------------------------------------------------
function updateCartBadge(count) {
    const badge = document.getElementById('cartBadge');
    if (badge) {
        badge.textContent = count;
        badge.style.transform = 'translate(30%, -30%) scale(1.4)';
        setTimeout(() => { badge.style.transform = 'translate(30%, -30%) scale(1)'; }, 200);
    }
}

// -------------------------------------------------------
// Add to cart (AJAX) — corrigido v3
// -------------------------------------------------------
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-add-cart]');
    if (!btn) return;
    e.preventDefault();

    const itemId   = btn.dataset.id;
    const itemType = btn.dataset.type || 'movie';
    const csrf     = document.querySelector('meta[name="csrf-token"]')?.content || '';

    btn.disabled = true;
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        const endpoint = itemType === 'combo' ? '/cart/combo' : '/cart/movie';
        const bodyData = itemType === 'combo'
            ? { combo_id: itemId, _csrf_token: csrf }
            : { movie_id: itemId, _csrf_token: csrf };

        const res = await fetch(`${window.APP_URL}${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(bodyData)
        });

        const data = await res.json();

        // Não logado → redireciona para login
        if (data.error === 'login_required') {
            showToast('Faça login para adicionar ao carrinho.', 'warning');
            setTimeout(() => { window.location.href = data.redirect || window.APP_URL + '/login'; }, 1500);
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            return;
        }

        if (data.error) {
            showToast(data.error, 'error');
        } else {
            // Atualiza badge
            const badge = document.getElementById('cartBadge');
            if (badge && data.cartCount !== undefined) {
                badge.textContent = data.cartCount;
                badge.style.transform = 'scale(1.5)';
                setTimeout(() => badge.style.transform = '', 300);
            }

            // Feedback no botão
            btn.innerHTML = '<i class="bi bi-check-lg"></i>';
            btn.style.background = 'linear-gradient(135deg,#059669,#10b981)';
            showToast(data.message || 'Adicionado ao carrinho!',
                      data.status === 'info' ? 'info' : 'success');

            // Se estamos na página do carrinho, recarrega sem refresh completo
            if (window.location.pathname.includes('/cart') && data.status !== 'info') {
                refreshCartTable();
            }

            setTimeout(() => {
                btn.style.background = '';
                btn.innerHTML = originalHTML;
            }, 2000);
        }
    } catch (err) {
        showToast('Erro ao adicionar. Tente novamente.', 'error');
    } finally {
        btn.disabled = false;
    }
});

// -------------------------------------------------------
// Remove from cart
// -------------------------------------------------------
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-remove-cart]');
    if (!btn) return;
    e.preventDefault();

    const key  = btn.dataset.key;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const row  = btn.closest('tr') || btn.closest('[data-cart-row]');

    // Feedback imediato
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        const res  = await fetch(`${window.APP_URL}/cart/remove`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ key, _csrf_token: csrf })
        });

        const data = await res.json();

        if (data.success) {
            if (row) {
                row.style.transition = 'opacity .25s, transform .25s';
                row.style.opacity    = '0';
                row.style.transform  = 'translateX(-16px)';
                setTimeout(() => { row.remove(); recalcCart(); }, 260);
            }
            // Atualiza badge
            const badge = document.getElementById('cartBadge');
            if (badge && data.cartCount !== undefined) {
                badge.textContent = data.cartCount;
            }
            showToast('Item removido do carrinho.', 'info');
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash"></i>';
            showToast('Não foi possível remover o item.', 'error');
        }
    } catch (err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-trash"></i>';
        showToast('Erro ao remover. Tente novamente.', 'error');
    }
});


// -------------------------------------------------------
// Qty +/- controls
// -------------------------------------------------------
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-qty-change]');
    if (!btn) return;
    e.preventDefault();

    const key    = btn.dataset.key;
    const change = parseInt(btn.dataset.qtyChange);
    const csrf   = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const qtyEl  = document.getElementById('qty-' + key);
    const totEl  = document.getElementById('total-' + key);

    // Optimistic UI
    const curQty = parseInt(qtyEl?.textContent || '1');
    if (curQty + change < 1) return;

    btn.disabled = true;
    try {
        const res  = await fetch(`${window.APP_URL}/cart/qty`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ key, change, _csrf_token: csrf })
        });
        const data = await res.json();
        if (data.success) {
            if (qtyEl)  qtyEl.textContent  = data.qty;
            if (totEl)  totEl.textContent   = data.itemTotal;
            // Update subtotal
            const subEl = document.getElementById('cartSubtotal');
            if (subEl)  subEl.textContent   = data.subtotal;
            const totCartEl = document.getElementById('cartTotal');
            if (totCartEl)  totCartEl.textContent = data.subtotal;
            // Badge
            const badge = document.getElementById('cartBadge');
            if (badge)  badge.textContent = data.cartCount;
        }
    } catch(err) { /* silently fail */ }
    finally { btn.disabled = false; }
});

// -------------------------------------------------------
// Coupon application
// -------------------------------------------------------
const couponForm = document.getElementById('couponForm');
if (couponForm) {
    couponForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const code = couponForm.querySelector('input[name="coupon"]').value.trim();
        const msgEl = document.getElementById('couponMsg');

        try {
            const res = await fetch(`${window.APP_URL}/cart/coupon`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ coupon: code })
            });
            const data = await res.json();
            if (data.error) {
                if (msgEl) { msgEl.textContent = data.error; msgEl.className = 'small text-danger mt-1'; }
            } else {
                if (msgEl) { msgEl.textContent = `Coupon "${data.code}" applied!`; msgEl.className = 'small text-success mt-1'; }
                location.reload();
            }
        } catch (err) {
            if (msgEl) { msgEl.textContent = 'Failed to apply coupon.'; msgEl.className = 'small text-danger mt-1'; }
        }
    });
}

// -------------------------------------------------------
// Favorites toggle (AJAX)
// -------------------------------------------------------
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.fav-btn');
    if (!btn) return;
    e.preventDefault();

    const movieId = btn.dataset.movieId;
    try {
        const res = await fetch(`${window.APP_URL}/favorites/toggle`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ movie_id: movieId })
        });
        const data = await res.json();
        btn.classList.toggle('active', data.action === 'added');
        showToast(data.action === 'added' ? 'Added to favorites!' : 'Removed from favorites', data.action === 'added' ? 'success' : 'info');
    } catch (err) {
        showToast('Please log in to use favorites.', 'warning');
    }
});

// -------------------------------------------------------
// Star rating interactive
// -------------------------------------------------------
const starContainers = document.querySelectorAll('.star-rating-interactive');
starContainers.forEach(container => {
    const stars = container.querySelectorAll('label');
    stars.forEach((star, i) => {
        star.addEventListener('mouseenter', () => {
            stars.forEach((s, j) => s.style.color = j >= stars.length - 1 - i ? 'var(--gold)' : 'var(--dark-5)');
        });
        star.addEventListener('mouseleave', () => {
            stars.forEach(s => s.style.color = '');
        });
    });
});

// -------------------------------------------------------
// Toast notifications
// -------------------------------------------------------
function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast show align-items-center text-white border-0 toast-${type} mb-2`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>`;
    container.appendChild(toast);
    const bsToast = bootstrap.Toast.getOrCreateInstance(toast, { delay: 3500 });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

function createToastContainer() {
    const el = document.createElement('div');
    el.id = 'toastContainer';
    el.className = 'toast-container position-fixed top-0 end-0 p-3';
    el.style.zIndex = '9999';
    document.body.appendChild(el);
    return el;
}

// -------------------------------------------------------
// Recalculate cart totals on removal
// -------------------------------------------------------
function recalcCart() {
    const rows = document.querySelectorAll('[data-cart-price]');
    let subtotal = 0;
    rows.forEach(row => { subtotal += parseFloat(row.dataset.cartPrice) || 0; });
    const subEl = document.getElementById('cartSubtotal');
    const totalEl = document.getElementById('cartTotal');
    if (subEl) subEl.textContent = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
    if (totalEl) totalEl.textContent = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
}

// -------------------------------------------------------
// Refresh cart table without full page reload
// -------------------------------------------------------
async function refreshCartTable() {
    try {
        const res  = await fetch(window.APP_URL + '/cart', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Cart-Partial': '1' }
        });

        if (!res.ok) { window.location.reload(); return; }

        const html   = await res.text();
        const parser = new DOMParser();
        const doc    = parser.parseFromString(html, 'text/html');

        // Atualiza tbody da tabela
        const newTbody = doc.querySelector('.cart-table tbody');
        const curTbody = document.querySelector('.cart-table tbody');
        if (newTbody && curTbody) {
            curTbody.innerHTML = newTbody.innerHTML;
        }

        // Se carrinho ficou vazio
        const emptyState = doc.querySelector('.empty-state');
        if (emptyState && !newTbody) {
            const cartTable = document.querySelector('.cart-table');
            if (cartTable) cartTable.outerHTML = emptyState.outerHTML;
        }

        // Atualiza totais
        ['cartSubtotal','cartTotal'].forEach(id => {
            const newEl = doc.getElementById(id);
            const curEl = document.getElementById(id);
            if (newEl && curEl) curEl.textContent = newEl.textContent;
        });

    } catch(e) {
        // Fallback seguro
        window.location.reload();
    }
}

// -------------------------------------------------------
// Lazy load images with IntersectionObserver
// -------------------------------------------------------
if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                const img = e.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    io.unobserve(img);
                }
            }
        });
    }, { rootMargin: '200px' });
    document.querySelectorAll('img[data-src]').forEach(img => io.observe(img));
}

// -------------------------------------------------------
// Set APP_URL for fetch calls (injected by PHP in layout)
// -------------------------------------------------------
window.APP_URL = document.querySelector('meta[name="app-url"]')?.content || '';
