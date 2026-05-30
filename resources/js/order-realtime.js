/**
 * Realtime order-status updates (Phase 8).
 *
 * Subscribes the customer's browser to the *private* per-order channel and
 * live-updates the status badge when the order transitions, without any page
 * reload or polling. It is a no-op on pages that do not render the badge, so it
 * can be loaded globally from app.js.
 *
 * Activation contract (rendered by the order detail Blade view):
 *   <span id="order-status-badge"
 *         data-order-id="123"
 *         data-status="pending"
 *         data-updated-message="Order status updated">…</span>
 */

// Bootstrap contextual colour per status. Mirrors the Blade badge component so
// the JS-driven update is visually identical to a server-rendered badge.
const STATUS_VARIANTS = {
    pending: 'secondary',
    confirmed: 'info',
    processing: 'primary',
    shipped: 'warning',
    completed: 'success',
    cancelled: 'danger',
};

function applyStatus(badge, status, label) {
    const variant = STATUS_VARIANTS[status] ?? 'secondary';

    // Drop any previous text-bg-* colour, then apply the new one.
    badge.classList.forEach((cls) => {
        if (cls.startsWith('text-bg-')) {
            badge.classList.remove(cls);
        }
    });
    badge.classList.add(`text-bg-${variant}`);

    badge.dataset.status = status;
    badge.textContent = label ?? status;
}

function showToast(message) {
    if (!message || !window.bootstrap?.Toast) {
        return;
    }

    let container = document.getElementById('realtime-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'realtime-toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '1090';
        document.body.appendChild(container);
    }

    const toastEl = document.createElement('div');
    toastEl.className = 'toast align-items-center text-bg-dark border-0';
    toastEl.setAttribute('role', 'status');
    toastEl.setAttribute('aria-live', 'polite');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast" aria-label="Close"></button>
        </div>`;

    container.appendChild(toastEl);

    const toast = new window.bootstrap.Toast(toastEl, { delay: 4000 });
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    toast.show();
}

function initOrderStatusRealtime() {
    const badge = document.getElementById('order-status-badge');
    const orderId = badge?.dataset.orderId;

    if (!badge || !orderId || !window.Echo) {
        return;
    }

    window.Echo.private(`orders.${orderId}`)
        .listen('.order.status.changed', (payload) => {
            applyStatus(badge, payload.status, payload.status_label);
            showToast(badge.dataset.updatedMessage);
        });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initOrderStatusRealtime);
} else {
    initOrderStatusRealtime();
}

