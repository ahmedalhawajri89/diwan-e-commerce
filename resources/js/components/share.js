// Share this piece: the phone's own share sheet where there is one, and a
// copied link where there is not.
//
//   <button x-data="share" @click="share" data-url="…" data-title="…"
//           data-copied="Link copied" data-failed="Could not copy the link">
//
// The words come from the page, so this file carries no language of its own.
// A copied link is invisible, so it gets a toast; a share sheet speaks for
// itself, and a visitor who closes it has not failed at anything.

import { toast } from './toast.js';

export default () => ({
    async share() {
        const { url, title, copied, failed } = this.$el.dataset;

        if (navigator.share) {
            try {
                await navigator.share({ title, url });
            } catch {
                // Dismissed: nothing to report.
            }

            return;
        }

        try {
            await navigator.clipboard.writeText(url);
            toast(copied, 'ok');
        } catch {
            toast(failed, 'danger');
        }
    },
});
