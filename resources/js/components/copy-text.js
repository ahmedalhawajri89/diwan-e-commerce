// Copy a piece of text the page holds: an address to paste into a courier's
// form, an order number into a message.
//
//   <button x-data="copyText" @click="copy" data-text="…" data-copied="…" data-failed="…">
//
// A copy cannot be seen happening, so it says so in a toast.

import { toast } from './toast.js';

export default () => ({
    async copy() {
        const { text, copied, failed } = this.$el.dataset;

        try {
            await navigator.clipboard.writeText(text);
            toast(copied, 'ok');
        } catch {
            toast(failed, 'danger');
        }
    },
});
