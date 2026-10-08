// The product form's "how does it arrive": the size of the largest part is
// asked only when the piece comes apart or in boxes.
//
//   <fieldset x-data="arrivalPicker" @change="pick">
//     <input type="radio" name="arrives" value="parts">…
//     <div x-show="apart">…</div>
//   </fieldset>

export default () => ({
    value: '',

    init() {
        this.value = this.$root.querySelector('input[name="arrives"]:checked')?.value ?? '';
    },

    pick(event) {
        if (event.target.name === 'arrives') this.value = event.target.value;
    },

    get apart() {
        return this.value === 'parts' || this.value === 'flat';
    },
});
