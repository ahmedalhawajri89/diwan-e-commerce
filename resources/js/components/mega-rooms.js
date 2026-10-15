// The browse panel's rooms: pointing at a room, or tabbing to it, shows that
// room's preview beside the list.
//
//   <div x-data="megaRooms">
//     <ul @mouseover="hover" @focusin="hover"> <a data-room="0">…</a> … </ul>
//     <div data-preview="0">…</div> <div data-preview="1" inert>…</div> …
//   </div>
//
// Every preview is rendered by the server; this only chooses which shows.
// The hidden ones are inert, so Tab never walks into a room the visitor
// cannot see.

export default () => ({
    current: 0,

    init() {
        this.$watch('current', () => this.show());
        this.show();
    },

    hover(event) {
        const room = event.target.closest('[data-room]');
        if (room) this.current = Number(room.dataset.room);
    },

    show() {
        this.$root.querySelectorAll('[data-room]').forEach((link) => {
            link.toggleAttribute('data-current', Number(link.dataset.room) === this.current);
        });

        this.$root.querySelectorAll('[data-preview]').forEach((preview) => {
            const on = Number(preview.dataset.preview) === this.current;
            preview.classList.toggle('opacity-100', on);
            preview.classList.toggle('opacity-0', !on);
            preview.inert = !on;
            preview.setAttribute('aria-hidden', on ? 'false' : 'true');
        });
    },
});
