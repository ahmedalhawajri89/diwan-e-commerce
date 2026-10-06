// Show and hide one region: a filter group, an FAQ answer, a nav section.
//
//   <div x-data="disclosure">
//     <button @click="toggle" :aria-expanded="open">…</button>
//     <div x-show="open" x-cloak>…</div>
//   </div>
//
// Starts open when the element carries `data-open`, so a server-rendered
// state (a filter that is already applied) is not hidden on load.

export default () => ({
    open: false,

    init() {
        this.open = this.$el.hasAttribute('data-open');
    },

    toggle() {
        this.open = !this.open;
    },
});
