// The admin panel's frame: a rail that folds to icons, and below `lg` opens
// as a drawer.
//
//   <div x-data="adminShell" data-folded? :data-folded="folded" :data-drawer="drawer">
//     <button @click="fold">…</button>        (desktop: icons only, or not)
//     <button @click="openDrawer">…</button>  (phone: the rail as a drawer)
//   </div>
//
// The folded state is written to a cookie the server reads, so the next page
// is drawn at the right width from its first paint. The drawer is never
// remembered: a phone opens on the page, not on the menu.

const COOKIE = 'admin_rail';

export default () => ({
    folded: false,
    drawer: false,

    init() {
        this.folded = this.$root.hasAttribute('data-folded');
    },

    fold() {
        this.folded = !this.folded;
        document.cookie = `${COOKIE}=${this.folded ? 'folded' : 'open'}; path=/; max-age=31536000; samesite=lax`;
    },

    get foldLabel() {
        return this.folded ? this.$root.dataset.unfoldLabel : this.$root.dataset.foldLabel;
    },

    openDrawer() {
        this.drawer = true;
        this.$nextTick(() => this.$root.querySelector('#railPanel a')?.focus());
    },

    closeDrawer() {
        if (!this.drawer) return;
        this.drawer = false;
        this.$root.querySelector('[data-drawer-button]')?.focus();
    },
});
