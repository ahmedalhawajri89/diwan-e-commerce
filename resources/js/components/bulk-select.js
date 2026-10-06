// A list's selection, and the one form that acts on it.
//
//   <div x-data="bulkSelect" data-selected=":count selected" data-confirm=":count …">
//     <form id="bulkForm" x-ref="form" …>
//       <select name="action" x-ref="action" @change="chose">…</select>
//     </form>
//     <input type="checkbox" data-select-all @change="all">
//     <input type="checkbox" name="ids[]" form="bulkForm" data-select-row @change="count">
//   </div>
//
// The row boxes sit in the table and belong to the form by its id, because
// each row already holds its own forms (edit, delete) and forms cannot nest.
// The bar slides up from the foot of the page while anything is chosen.
// Choosing "delete" turns the form into a .delete-form, so the panel's own
// confirmation dialog asks first, naming how many.

export default () => ({
    selected: 0,

    get any() {
        return this.selected > 0;
    },

    get none() {
        return this.selected === 0;
    },

    get label() {
        return this.$root.dataset.selected.replace(':count', this.selected);
    },

    rows() {
        return [...this.$root.querySelectorAll('[data-select-row]')];
    },

    count() {
        const rows = this.rows();
        this.selected = rows.filter((box) => box.checked).length;

        const all = this.$root.querySelector('[data-select-all]');
        if (all) {
            all.checked = rows.length > 0 && this.selected === rows.length;
            all.indeterminate = this.selected > 0 && this.selected < rows.length;
        }

        this.chose();
    },

    all(event) {
        this.rows().forEach((box) => {
            box.checked = event.target.checked;
        });
        this.count();
    },

    clear() {
        this.rows().forEach((box) => {
            box.checked = false;
        });
        this.count();
    },

    /** "Delete" asks first; every other action does not. */
    chose() {
        const form = this.$refs.form;
        const destroying = this.$refs.action.value === 'delete';

        form.classList.toggle('delete-form', destroying);
        form.dataset.confirmMessage = this.$root.dataset.confirm.replace(':count', this.selected);
        delete form.dataset.confirmed;
    },
});
