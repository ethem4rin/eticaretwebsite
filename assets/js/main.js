/* =====================================================
   ÉLÉGANCE — Main JavaScript
   ===================================================== */

const SITE_URL = (() => {
  const meta = document.querySelector('meta[name="site-url"]');
  return meta ? meta.content.replace(/\/$/, '') : '';
})();

/* ===== DOM READY ===== */
document.addEventListener('DOMContentLoaded', () => {
  initMobileNav();
  initPasswordToggles();
  initAddToCartButtons();
  initCartPage();
  initQuantitySelectors();
  updateCartBadge();
  createToastContainer();
});

/* ===== MOBILE NAV ===== */
function initMobileNav() {
  const hamburger = document.getElementById('hamburger');
  const nav = document.getElementById('main-nav');
  if (!hamburger || !nav) return;
  hamburger.addEventListener('click', () => {
    const open = nav.classList.toggle('nav--open');
    hamburger.classList.toggle('is-active', open);
    hamburger.setAttribute('aria-expanded', open);
  });
  document.addEventListener('click', (e) => {
    if (!hamburger.contains(e.target) && !nav.contains(e.target)) {
      nav.classList.remove('nav--open');
      hamburger.classList.remove('is-active');
      hamburger.setAttribute('aria-expanded', 'false');
    }
  });
}

/* ===== PASSWORD TOGGLE ===== */
function initPasswordToggles() {
  document.querySelectorAll('.password-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.getElementById(btn.dataset.target);
      if (!target) return;
      target.type = target.type === 'password' ? 'text' : 'password';
    });
  });
}

/* ===== ADD TO CART ===== */
window.addToCart = async function(productId, quantity = 1) {
  try {
    const res = await fetch(`${SITE_URL}/api/cart.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'add', product_id: productId, quantity })
    });
    const data = await res.json();
    if (data.error === 'Not authenticated') {
      showToast('Please log in to add items to your cart.', 'error');
      setTimeout(() => { window.location.href = `${SITE_URL}/login.php`; }, 1200);
      return;
    }
    if (data.success) {
      setCartBadge(data.count);
      showToast('Added to cart', 'success');
    } else {
      showToast(data.error || 'Could not add to cart', 'error');
    }
  } catch {
    showToast('Network error. Please try again.', 'error');
  }
};

function initAddToCartButtons() {
  document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const productId = parseInt(btn.dataset.productId, 10);
      if (!productId) return;
      addToCart(productId, 1);
    });
  });
}

/* ===== CART BADGE ===== */
async function updateCartBadge() {
  try {
    const res = await fetch(`${SITE_URL}/api/cart.php?action=count`);
    const data = await res.json();
    setCartBadge(data.count || 0);
  } catch { /* silent */ }
}

function setCartBadge(count) {
  const badge = document.getElementById('cart-badge');
  if (!badge) return;
  badge.textContent = count > 0 ? count : '';
}

/* ===== QUANTITY SELECTORS (generic) ===== */
function initQuantitySelectors() {
  document.querySelectorAll('.quantity-selector').forEach(selector => {
    const minus = selector.querySelector('.qty-btn--minus');
    const plus  = selector.querySelector('.qty-btn--plus');
    const input = selector.querySelector('.qty-input');
    if (!minus || !plus || !input) return;
    if (input.classList.contains('cart-qty-input')) return; // handled by cart
    const max = parseInt(input.max, 10) || 99;
    minus.addEventListener('click', () => {
      const v = parseInt(input.value, 10);
      if (v > 1) input.value = v - 1;
    });
    plus.addEventListener('click', () => {
      const v = parseInt(input.value, 10);
      if (v < max) input.value = v + 1;
    });
  });
}

/* ===== CART PAGE ===== */
function initCartPage() {
  const cartTbody = document.getElementById('cart-tbody');
  if (!cartTbody) return;

  // Delegated events
  cartTbody.addEventListener('click', async (e) => {
    const removeBtn = e.target.closest('.remove-cart-btn');
    const qtyBtn    = e.target.closest('.cart-qty-btn');

    if (removeBtn) {
      const cartId = parseInt(removeBtn.dataset.cartId, 10);
      await removeCartItem(cartId, removeBtn.closest('tr'));
    }
    if (qtyBtn) {
      const cartId = parseInt(qtyBtn.dataset.cartId, 10);
      const action = qtyBtn.dataset.action;
      const row    = qtyBtn.closest('tr');
      const input  = row.querySelector('.cart-qty-input');
      if (!input) return;
      let qty = parseInt(input.value, 10);
      const max = parseInt(input.max, 10) || 99;
      if (action === 'minus' && qty > 1) qty--;
      else if (action === 'plus' && qty < max) qty++;
      else return;
      await updateCartItem(cartId, qty, row, input);
    }
  });
}

async function removeCartItem(cartId, row) {
  try {
    const res  = await fetch(`${SITE_URL}/api/cart.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'remove', cart_id: cartId })
    });
    const data = await res.json();
    if (data.success) {
      row.remove();
      setCartBadge(data.count);
      recalcCartTotals();
      showToast('Item removed', 'success');
    }
  } catch { showToast('Error removing item', 'error'); }
}

const cartUpdateTimers = {};
async function updateCartItem(cartId, qty, row, input) {
  input.value = qty;
  row.querySelector('.cart-subtotal').textContent = '...';
  clearTimeout(cartUpdateTimers[cartId]);
  cartUpdateTimers[cartId] = setTimeout(async () => {
    try {
      const res  = await fetch(`${SITE_URL}/api/cart.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update', cart_id: cartId, quantity: qty })
      });
      const data = await res.json();
      if (data.success) {
        input.value = data.quantity;
        const price = parseFloat(row.querySelector('.cart-price').dataset.price) || 0;
        row.querySelector('.cart-subtotal').textContent = '$' + (price * data.quantity).toFixed(2);
        setCartBadge(data.count);
        recalcCartTotals();
      }
    } catch { showToast('Error updating cart', 'error'); }
  }, 350);
}

function recalcCartTotals() {
  let subtotal = 0;
  document.querySelectorAll('.cart-row').forEach(row => {
    const price = parseFloat(row.querySelector('.cart-price')?.dataset.price) || 0;
    const qty   = parseInt(row.querySelector('.cart-qty-input')?.value, 10) || 0;
    subtotal += price * qty;
  });
  const subtotalEl = document.getElementById('cart-subtotal');
  const totalEl    = document.getElementById('cart-total');
  const shipping   = subtotal >= 150 ? 0 : 9.99;
  if (subtotalEl) subtotalEl.textContent = '$' + subtotal.toFixed(2);
  if (totalEl)    totalEl.textContent    = '$' + (subtotal + shipping).toFixed(2);
  const shippingEl = document.querySelector('.cart-summary__shipping');
  if (shippingEl) shippingEl.textContent = shipping === 0 ? 'Free' : '$9.99';

  // Hide checkout btn if empty
  const rows = document.querySelectorAll('.cart-row');
  if (rows.length === 0) {
    const checkoutBtn = document.querySelector('.cart-checkout-btn');
    if (checkoutBtn) checkoutBtn.style.display = 'none';
  }
}

/* ===== TOAST NOTIFICATIONS ===== */
function createToastContainer() {
  if (document.querySelector('.toast-container')) return;
  const container = document.createElement('div');
  container.className = 'toast-container';
  document.body.appendChild(container);
}

function showToast(message, type = 'success') {
  const container = document.querySelector('.toast-container');
  if (!container) return;
  const toast = document.createElement('div');
  toast.className = `toast toast--${type}`;
  const icon = type === 'success'
    ? '<svg class="toast__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>'
    : '<svg class="toast__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
  toast.innerHTML = icon + '<span>' + message + '</span>';
  container.appendChild(toast);
  requestAnimationFrame(() => { requestAnimationFrame(() => { toast.classList.add('is-visible'); }); });
  setTimeout(() => {
    toast.classList.remove('is-visible');
    toast.addEventListener('transitionend', () => toast.remove(), { once: true });
  }, 3000);
}
