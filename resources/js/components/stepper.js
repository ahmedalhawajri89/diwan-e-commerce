// A quantity with − and + either side of it.
//
//   <div x-data="stepper">
//     <button @click="dec" :disabled="atMin">−</button>
//     <input x-ref="input" type="number" min="1" max="12" @change="read">
//     <button @click="inc" :disabled="atMax">+</button>
//   </div>
//
// The input is a real number field with a name, so the form works without
// JavaScript and the value is whatever is typed. The buttons only step it,
// within the input's own min and max, which the server set from real stock.
// Nothing here submits the form: a keyboard user stepping from 1 to 3 must not
// reload the page twice on the way.

export default () => ({
    value: 1,
    min: 1,
    max: 999,

    init() {
        const input = this.$refs.input;
        this.min = Number(input.min) || 1;
        this.max = Number(input.max) || 999;
        this.value = Number(input.value) || this.min;
    },

    get atMin() {
        return this.value <= this.min;
    },

    get atMax() {
        return this.value >= this.max;
    },

    dec() {
        if (!this.atMin) this.set(this.value - 1);
    },

    inc() {
        if (!this.atMax) this.set(this.value + 1);
    },

    read() {
        const typed = Math.round(Number(this.$refs.input.value));
        this.set(Number.isFinite(typed) ? typed : this.min);
    },

    set(n) {
        this.value = Math.min(this.max, Math.max(this.min, n));
        this.$refs.input.value = this.value;
    },
});
