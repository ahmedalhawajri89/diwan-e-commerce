// Print the page, from a button: the packing slip's.
//
//   <div x-data="printPage"><button @click="print">Print</button></div>

export default () => ({
    print() {
        window.print();
    },
});
