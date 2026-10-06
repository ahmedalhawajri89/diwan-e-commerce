// A menu that drops from a button: the account menu, the saved-pieces peek.
//
//   <div x-data="dropdown" @click.outside="close" @keydown.escape="dismiss">
//     <button x-ref="button" @click="toggle" :aria-expanded="open">…</button>
//     <div x-show="open" x-cloak>…</div>
//   </div>

export default () => ({
    open: false,

    toggle() {
        this.open = !this.open;
    },

    close() {
        this.open = false;
    },

    /** Escape closes and hands focus back to the button that opened it. */
    dismiss() {
        if (!this.open) return;

        this.open = false;
        this.$refs.button?.focus();
    },
});
