// A piece's stock, edited where it is listed: a small panel with one field
// per colour, saved without leaving the list.
//
//   <div x-data="stockEditor" data-saved="…" data-failed="…" data-out="…" data-low="…" data-low-line="3">
//     <button x-ref="button" @click="toggle"><span x-ref="total">12</span><span x-ref="chip"></span></button>
//     <div x-show="open" @keydown.enter.prevent="save">
//       <div data-stock-row data-url="…/variants/7/stock" data-original="5">
//         <button data-step="-1" @click="step">−</button><input name="stock"><button data-step="1" @click="step">+</button>
//       </div>
//       <button @click="save">Save</button> <span x-ref="status"></span>
//     </div>
//   </div>
//
// Only the colours whose figure changed are sent, one request each, and the
// list's total and its sold-out / low mark follow the server's answer, not
// the typed figure. Every save is a line in the stock log.

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const CHIP = {
    out: 'bg-danger-wash text-danger',
    low: 'bg-warn-wash text-warn',
};

export default () => ({
    open: false,
    busy: false,

    get idle() {
        return !this.busy;
    },

    toggle() {
        this.open = !this.open;
        if (this.open) {
            this.say('');
            this.$nextTick(() => this.$root.querySelector('[data-stock-row] input')?.select());
        }
    },

    close() {
        this.open = false;
    },

    dismiss() {
        if (!this.open) return;
        this.open = false;
        this.$refs.button?.focus();
    },

    step(event) {
        const input = event.currentTarget.closest('[data-stock-row]').querySelector('input');
        input.value = Math.max(0, (parseInt(input.value, 10) || 0) + Number(event.currentTarget.dataset.step));
    },

    async save() {
        if (this.busy) return;

        const changed = [...this.$root.querySelectorAll('[data-stock-row]')].filter((row) => {
            const input = row.querySelector('input');
            return input.value !== '' && Number(input.value) !== Number(row.dataset.original);
        });

        if (!changed.length) {
            this.close();
            return;
        }

        this.busy = true;
        let last = null;

        try {
            for (const row of changed) {
                const input = row.querySelector('input');
                const response = await fetch(row.dataset.url, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                    credentials: 'same-origin',
                    body: JSON.stringify({ stock: Number(input.value) }),
                });
                if (!response.ok) throw new Error(String(response.status));

                last = await response.json();
                row.dataset.original = String(last.stock);
                input.value = String(last.stock);
            }

            this.$refs.total.textContent = String(last.total);
            this.paintChip(last.totalState);
            this.say(this.$root.dataset.saved);
            setTimeout(() => this.close(), 700);
        } catch {
            this.say(this.$root.dataset.failed, true);
        } finally {
            this.busy = false;
        }
    },

    paintChip(state) {
        const chip = this.$refs.chip;
        chip.hidden = !CHIP[state];
        chip.className = `inline-flex items-center px-2 py-0.5 rounded-pill font-label text-[0.6875rem] font-medium leading-none ${CHIP[state] ?? ''}`;
        chip.textContent = state === 'out' ? this.$root.dataset.out : state === 'low' ? this.$root.dataset.low : '';
    },

    say(text, bad = false) {
        const status = this.$refs.status;
        status.textContent = text;
        status.className = `text-xs ${bad ? 'text-danger' : 'text-ok'}`;
    },
});
