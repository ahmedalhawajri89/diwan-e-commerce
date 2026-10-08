// "Write a draft": asks the server for a first draft of both descriptions,
// from what the form says about the piece (and, on a saved piece, its
// photograph), then puts it in the form for the operator to read and change.
//
//   <div x-data="descriptionDraft" data-url="…" data-product="12" data-done="…">
//     <button @click="write" :disabled="busy">…</button>
//     <div x-show="hasPreview"> <p x-ref="previewEn"></p> <p x-ref="previewAr"></p>
//       <button @click="apply">…</button> <button @click="discard">…</button> </div>
//     <p x-ref="status"></p>
//   </div>
//
// Empty fields are filled at once. Fields that already hold text are never
// overwritten silently: the draft is shown beside them and replaces them
// only when asked. Nothing is saved here; the form's own Save does that.

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export default () => ({
    busy: false,
    hasPreview: false,
    draft: null,

    get idle() {
        return !this.busy;
    },

    field(name) {
        return this.$root.closest('form')?.querySelector(`[name="${name}"]`);
    },

    value(name) {
        return this.field(name)?.value?.trim() ?? '';
    },

    async write() {
        if (this.busy) return;

        if (!this.value('name') && !this.$root.dataset.product) {
            this.say(this.$root.dataset.needName, true);
            this.field('name')?.focus();
            return;
        }

        this.busy = true;
        this.hasPreview = false;
        this.say(this.$root.dataset.working);

        const body = {
            product_id: this.$root.dataset.product ? Number(this.$root.dataset.product) : null,
            name: this.value('name') || null,
            name_ar: this.value('name_ar') || null,
            category_id: this.value('category_id') ? Number(this.value('category_id')) : null,
            color: this.value('color') || null,
            width_cm: this.value('width_cm') ? Number(this.value('width_cm')) : null,
            depth_cm: this.value('depth_cm') ? Number(this.value('depth_cm')) : null,
            height_cm: this.value('height_cm') ? Number(this.value('height_cm')) : null,
        };

        try {
            const response = await fetch(this.$root.dataset.url, {
                method: 'POST',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                credentials: 'same-origin',
                body: JSON.stringify(body),
            });
            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                this.say(data.message || this.$root.dataset.failed, true);
                return;
            }

            this.draft = data;
            if (!this.value('description') && !this.value('description_ar')) {
                this.apply();
            } else {
                this.$refs.previewEn.textContent = data.en;
                this.$refs.previewAr.textContent = data.ar;
                this.hasPreview = true;
                this.say(this.$root.dataset.ready);
            }
        } catch {
            this.say(this.$root.dataset.failed, true);
        } finally {
            this.busy = false;
        }
    },

    apply() {
        if (!this.draft) return;

        this.put('description', this.draft.en);
        this.put('description_ar', this.draft.ar);
        if (this.draft.name_ar && !this.value('name_ar')) this.put('name_ar', this.draft.name_ar);

        this.hasPreview = false;
        this.say(this.$root.dataset.done);
        this.field('description')?.focus();
    },

    discard() {
        this.hasPreview = false;
        this.draft = null;
        this.say('');
    },

    put(name, text) {
        const input = this.field(name);
        if (!input) return;
        input.value = text;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        // Marked as a draft until the operator edits it.
        input.classList.add('bg-accent-wash');
        input.addEventListener('input', () => input.classList.remove('bg-accent-wash'), { once: true });
    },

    say(text, bad = false) {
        this.$refs.status.textContent = text;
        this.$refs.status.className = `m-0 mt-2 text-sm ${bad ? 'text-danger' : 'text-neutral'}`;
    },
});
