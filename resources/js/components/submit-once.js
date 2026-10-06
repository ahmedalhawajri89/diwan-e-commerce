// A form that can only be sent once per press.
//
//   <form x-data="submitOnce" @submit="lock">
//     <button :disabled="busy"><span x-show="idle">Sign in</span><span x-show="busy" x-cloak>Signing in…</span></button>
//   </form>
//
// A slow connection invites a second click, and a second sign-in or a second
// account creation is a confusing error for nobody's benefit. The browser only
// fires `submit` once its own validation has passed, so a form with a missing
// field is never locked.

export default () => ({
    busy: false,

    get idle() {
        return !this.busy;
    },

    init() {
        // Back from the next page, the browser may restore this one from its
        // cache exactly as it was left: locked. Unlock it.
        window.addEventListener('pageshow', () => {
            this.busy = false;
        });
    },

    lock() {
        this.busy = true;
    },
});
