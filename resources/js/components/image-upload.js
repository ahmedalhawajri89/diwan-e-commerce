// The product form's photographs: a preview of each before it is sent, any
// one taken back, and a phone's 4000px original made a sensible size first.
//
//   <div x-data="imageUpload" data-ready="…" data-uploading="…">
//     <input type="file" x-ref="input" @change="pick" multiple>
//     <ul x-ref="list"></ul>
//     <p x-ref="status"></p>
//     <div x-show="uploading">…</div>
//   </div>
//
// Shrinking happens in the browser, so a photograph from a phone (often 3 to
// 8 MB) fits under the server's 2 MB limit and uploads in a fraction of the
// time. The longest edge becomes 2400px, which is more than the largest size
// the shop ever draws (1600px). A file already small enough is sent as it is.
// Without JavaScript the input is an ordinary file field.

import { icon } from '../furnish/interactions.js';

const LONGEST_EDGE = 2400;
const SMALL_ENOUGH = 1.5 * 1024 * 1024;
const QUALITY = 0.86;

const kb = (bytes) => (bytes >= 1024 * 1024 ? `${(bytes / 1024 / 1024).toFixed(1)} MB` : `${Math.max(1, Math.round(bytes / 1024))} KB`);

async function shrink(file) {
    // A GIF may move; it is sent untouched.
    if (file.type === 'image/gif' || typeof createImageBitmap !== 'function') return file;

    let bitmap;
    try {
        bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
    } catch {
        return file; // the server will say what is wrong with it
    }

    const scale = Math.min(1, LONGEST_EDGE / Math.max(bitmap.width, bitmap.height));
    if (scale === 1 && file.size <= SMALL_ENOUGH) {
        bitmap.close?.();
        return file;
    }

    const canvas = document.createElement('canvas');
    canvas.width = Math.round(bitmap.width * scale);
    canvas.height = Math.round(bitmap.height * scale);
    canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
    bitmap.close?.();

    // A PNG may be see-through; WebP keeps that, JPEG would paint it black.
    const type = file.type === 'image/png' ? 'image/webp' : 'image/jpeg';
    const blob = await new Promise((resolve) => canvas.toBlob(resolve, type, QUALITY));
    if (!blob || blob.size >= file.size) return file;

    const name = file.name.replace(/\.[^.]+$/, '') + (type === 'image/webp' ? '.webp' : '.jpg');
    return new File([blob], name, { type, lastModified: Date.now() });
}

export default () => ({
    files: [],
    preparing: false,
    uploading: false,

    init() {
        this.$root.closest('form')?.addEventListener('submit', (event) => {
            if (this.preparing) {
                event.preventDefault();
                return;
            }
            if (this.files.length) this.uploading = true;
        });

        window.addEventListener('pageshow', () => {
            this.uploading = false;
        });
    },

    get idle() {
        return !this.uploading;
    },

    async pick() {
        const chosen = [...this.$refs.input.files];
        if (!chosen.length) return;

        this.preparing = true;
        this.say(this.$root.dataset.preparing);

        const before = chosen.reduce((sum, f) => sum + f.size, 0);
        const ready = await Promise.all(chosen.map(shrink));
        this.files = [...this.files, ...ready.map((file, i) => ({ file, was: chosen[i].size, url: URL.createObjectURL(file) }))];

        this.preparing = false;
        this.sync(before);
    },

    remove(index) {
        URL.revokeObjectURL(this.files[index].url);
        this.files.splice(index, 1);
        this.sync();
    },

    /** Put the kept, shrunk files back into the input the form will send. */
    sync(before = null) {
        const transfer = new DataTransfer();
        this.files.forEach(({ file }) => transfer.items.add(file));
        this.$refs.input.files = transfer.files;
        this.paint();

        if (!this.files.length) {
            this.say('');
            return;
        }

        const now = this.files.reduce((sum, f) => sum + f.file.size, 0);
        const was = before ?? this.files.reduce((sum, f) => sum + f.was, 0);
        const line = this.$root.dataset.ready.replace(':count', this.files.length).replace(':size', kb(now));
        this.say(was > now * 1.1 ? `${line} ${this.$root.dataset.from.replace(':size', kb(was))}` : line);
    },

    say(text) {
        this.$refs.status.textContent = text;
    },

    paint() {
        const list = this.$refs.list;
        list.replaceChildren(...this.files.map(({ file, url }, index) => {
            const li = document.createElement('li');
            li.className = 'group relative aspect-[4/5] overflow-hidden bg-paper-2 motion-safe:animate-fade-up';

            const img = document.createElement('img');
            img.src = url;
            img.alt = '';
            img.className = 'absolute inset-0 size-full object-cover';

            const size = document.createElement('span');
            size.className = 'absolute inset-x-0 bottom-0 px-2 py-1 bg-paper/90 font-mono text-[0.6875rem] text-ink';
            size.dir = 'ltr';
            size.textContent = kb(file.size);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'absolute top-1.5 end-1.5 inline-flex items-center justify-center size-8 rounded-pill border-0 bg-paper text-ink cursor-pointer opacity-90 hover:opacity-100 focus-visible:outline-2 focus-visible:outline-focus';
            remove.setAttribute('aria-label', this.$root.dataset.remove.replace(':name', file.name));
            remove.innerHTML = icon('x');
            remove.addEventListener('click', () => this.remove(index));

            li.append(img, size, remove);
            return li;
        }));
    },
});
