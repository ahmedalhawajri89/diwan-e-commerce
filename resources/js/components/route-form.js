// The way-in dialog: the lift's measurements show only for a lift, the
// stairs' only for stairs.
//
//   <form x-data="routeForm" @change="pick">
//     <input type="radio" name="access" value="lift">…
//     <fieldset x-show="byLift">…</fieldset>
//   </form>

export default () => ({
    access: '',

    init() {
        this.access = this.$root.querySelector('input[name="access"]:checked')?.value ?? '';
    },

    pick(event) {
        if (event.target.name === 'access') this.access = event.target.value;
    },

    get byLift() {
        return this.access === 'lift';
    },

    get byStairs() {
        return this.access === 'stairs';
    },
});
