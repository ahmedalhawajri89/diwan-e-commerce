// The way-in dialog: the lift's measurements show only for a lift, the
// stairs' only for stairs, and a villa's inner stairs only once ticked.
//
//   <form x-data="routeForm" @change="pick">
//     <input type="radio" name="access" value="lift">…
//     <fieldset x-show="byLift">…</fieldset>
//   </form>

export default () => ({
    access: '',
    inner: false,

    init() {
        this.access = this.$root.querySelector('input[name="access"]:checked')?.value ?? '';
        this.inner = this.$root.querySelector('input[name="has_inner"]')?.checked ?? false;
    },

    pick(event) {
        if (event.target.name === 'access') this.access = event.target.value;
        if (event.target.name === 'has_inner') this.inner = event.target.checked;
    },

    get hasInner() {
        return this.inner;
    },

    get byLift() {
        return this.access === 'lift';
    },

    get byStairs() {
        return this.access === 'stairs';
    },
});
