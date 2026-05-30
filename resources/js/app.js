//

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';

// Bootstrap 5 JS (modals, dropdowns, tooltips, ...). Popper is bundled in.
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Realtime order-status updates (no-op on pages without the order badge).
import './order-realtime';

