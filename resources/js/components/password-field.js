// Show or hide what has been typed into a password field.
//
//   <div x-data="passwordField" data-show="Show" data-hide="Hide">
//     <input type="password" x-ref="input">
//     <button type="button" @click="toggle" :aria-pressed="shown"><span x-text="label"></span></button>
//   </div>
//
// The input's type is changed here, not in the template: the CSP build of
// Alpine refuses property assignments on DOM objects in an expression.

export default () => ({
    shown: false,

    get label() {
        // $root, not $el: evaluated from the label span, $el would be the span.
        return this.shown ? this.$root.dataset.hide : this.$root.dataset.show;
    },

    toggle() {
        this.shown = !this.shown;
        this.$refs.input.type = this.shown ? 'text' : 'password';

        // Keep the cursor where the visitor was typing.
        this.$refs.input.focus();
    },
});
