/* ═══════════════════════════════════════════

   <script src="cart.js"></script>
═══════════════════════════════════════════ */

function getCart() {
    try { return JSON.parse(localStorage.getItem('glowup_cart')) || []; }
    catch { return []; }
}
function saveCart(cart) {
    localStorage.setItem('glowup_cart', JSON.stringify(cart));
}
function getCartCount() {
    return getCart().reduce((s, i) => s + i.qty, 0);
}

/* Update all cart badges on the page */
function refreshBadge() {
    const count = getCartCount();
    document.querySelectorAll('#cartBadge').forEach(el => {
        el.textContent = count;
        el.style.transform = 'scale(1.5)';
        setTimeout(() => el.style.transform = '', 300);
    });
}

/* Add item to cart — call from any page */
function addToCartGlobal(name, price, img, cat) {
    const cart = getCart();
    const existing = cart.find(i => i.name === name);
    if (existing) {
        existing.qty++;
    } else {
        cart.push({ name, price: parseFloat(price), img: img || '', cat: cat || 'Glow Up', qty: 1 });
    }
    saveCart(cart);
    refreshBadge();
}

/* Make cart icon navigate to cart.html */
function initCartIcon() {
    document.querySelectorAll('.nav-icon-btn').forEach(btn => {
        btn.style.cursor = 'pointer';
        btn.addEventListener('click', () => { window.location.href = 'cart.html'; });
    });
    refreshBadge();
}

/* Run on page load */
document.addEventListener('DOMContentLoaded', () => {
    initCartIcon();
});