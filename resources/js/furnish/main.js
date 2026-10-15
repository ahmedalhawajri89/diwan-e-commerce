// The storefront's and the panel's plain modules — search, saved pieces,
// the admin's confirmations — started from one place. Loaded by app.js.
import './interactions.js';

import { initSearch } from './search.js';
import { initWishlist } from './wishlist.js';
import { initAdmin } from './admin.js';
import { initBagDrawer } from './bag-drawer.js';


// The storefront's behaviour, which until now lived in three <script> blocks
// inside layouts/furnish.blade.php. Started here rather than each module
// starting itself, so the order is visible in one place and a module can be
// dropped by deleting one line.
//
// A `type="module"` script is deferred, so the document may already be parsed
// by the time this runs — waiting unconditionally for DOMContentLoaded would
// wait for an event that has been and gone.
function start() {
    // Each bails out when its markup is absent, so the two layouts share one
    // bundle and neither pays for the other's behaviour beyond the bytes.
    initSearch();
    initWishlist();
    initAdmin();
    initBagDrawer();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
} else {
    start();
}
