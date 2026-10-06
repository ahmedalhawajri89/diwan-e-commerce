// The storefront's interactive parts: Alpine for state, the native <dialog>
// for anything modal.
//
// The CSP build of Alpine, because the policy forbids `unsafe-eval`. Each
// component's state and logic lives in a file under ./components and is
// registered by name; a template only names it (`x-data="dropdown"`) and calls
// its methods, so no JavaScript is written into the markup.

import Alpine from '@alpinejs/csp';

import './furnish/main.js';

import { initDialogs } from './components/dialogs.js';
import { initReveal } from './components/reveal.js';
import dropdown from './components/dropdown.js';
import disclosure from './components/disclosure.js';
import siteHeader from './components/site-header.js';
import fitDemo from './components/fit-demo.js';
import rulerMark from './components/ruler-mark.js';
import passwordField from './components/password-field.js';
import submitOnce from './components/submit-once.js';
import stepper from './components/stepper.js';
import gallery from './components/gallery.js';
import stickyBuy from './components/sticky-buy.js';
import share from './components/share.js';
import rail from './components/rail.js';

Alpine.data('dropdown', dropdown);
Alpine.data('disclosure', disclosure);
Alpine.data('siteHeader', siteHeader);
Alpine.data('fitDemo', fitDemo);
Alpine.data('rulerMark', rulerMark);
Alpine.data('passwordField', passwordField);
Alpine.data('submitOnce', submitOnce);
Alpine.data('stepper', stepper);
Alpine.data('gallery', gallery);
Alpine.data('stickyBuy', stickyBuy);
Alpine.data('share', share);
Alpine.data('rail', rail);

initDialogs();
initReveal();

window.Alpine = Alpine;
Alpine.start();
