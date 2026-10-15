// A slim "Add to bag" bar along the bottom of a phone's screen, shown once the
// page's own button has scrolled away above, and gone again when the footer
// arrives — so it never covers the last thing on the page.
//
//   <div x-data="stickyBuy" data-watch="addToBag" :aria-hidden="hidden" :inert="hidden">
//     <button type="submit" form="buyForm">Add to bag</button>
//   </div>
//
// The bar's button submits the page's form through the `form` attribute, so
// there is one form, one set of fields and one submit lock, not two.

export default () => ({
    pastButton: false,
    footerShowing: false,

    get shown() {
        return this.pastButton && !this.footerShowing;
    },

    get hidden() {
        return !this.shown;
    },

    init() {
        const button = document.getElementById(this.$el.dataset.watch);
        const footer = document.querySelector('footer');

        if (!button || !('IntersectionObserver' in window)) return;

        new IntersectionObserver(([entry]) => {
            // Only once it has gone off the TOP: before the visitor reaches it,
            // the button is below the fold and the bar would be a duplicate.
            this.pastButton = !entry.isIntersecting && entry.boundingClientRect.top < 0;
        }).observe(button);

        if (footer) {
            new IntersectionObserver(([entry]) => {
                this.footerShowing = entry.isIntersecting;
            }).observe(footer);
        }
    },
});
