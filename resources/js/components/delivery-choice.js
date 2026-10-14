// The checkout's city: choosing one shows its delivery fee, the new total
// and the VAT inside it at once. Each <option> carries those figures, already
// written by the server (App\Support\Money); nothing is worked out here.
//
//   <div x-data="deliveryChoice" data-fee="…" data-total="…" data-vat="…">
//     <select x-ref="zone" @change="pick"><option data-fee="…" data-total="…" data-vat="…">…</option></select>
//     <span x-text="fee"></span> <span x-text="total"></span> <span x-text="vat"></span>
//   </div>

export default () => ({
    fee: '',
    total: '',
    vat: '',
    label: '',

    init() {
        this.fee = this.$root.dataset.fee;
        this.total = this.$root.dataset.total;
        this.vat = this.$root.dataset.vat;
        this.label = this.$root.dataset.label;
        this.pick();
    },

    pick() {
        const option = this.$refs.zone?.selectedOptions[0];
        if (!option?.dataset.total) return;

        this.fee = option.dataset.fee;
        this.total = option.dataset.total;
        this.vat = option.dataset.vat;
        this.label = option.dataset.label;
    },
});
