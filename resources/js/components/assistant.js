// «Ask Diwan»: a short conversation in a drawer. The visitor's words go to
// the server; the answer comes back as a sentence and, below it, the pieces
// it points to, already drawn by the server from the catalogue (so their
// prices are the rows', never the model's words).
//
//   <div x-data="assistant" data-url="…" data-forget="…" data-failed="…" data-thinking="…">
//     <button x-ref="opener" @click="show">…</button>
//     <dialog x-ref="dialog" @close="closed">
//       <div x-ref="log">…</div>
//       <form @submit.prevent="send"><textarea x-ref="input" @keydown.enter="enter"></textarea></form>
//     </dialog>
//   </div>

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export default () => ({
    open: false,
    busy: false,

    show() {
        this.$refs.dialog.showModal();
        this.open = true;
        this.$nextTick(() => this.$refs.input.focus());
    },

    hide() {
        this.$refs.dialog.close();
    },

    closed() {
        this.open = false;
        this.$refs.opener?.focus();
    },

    /** Enter sends; Shift+Enter is a new line. */
    enter(event) {
        if (event.shiftKey || event.isComposing) return;
        event.preventDefault();
        this.send();
    },

    suggest(event) {
        this.$refs.input.value = event.currentTarget.dataset.text;
        this.send();
    },

    async send() {
        const text = this.$refs.input.value.trim();
        if (text.length < 2 || this.busy) return;

        this.busy = true;
        this.$refs.input.value = '';
        this.$refs.suggestions?.closest('div')?.classList.add('hidden');
        this.bubble(text, 'user');
        const waiting = this.thinking();

        try {
            const response = await fetch(this.$root.dataset.url, {
                method: 'POST',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                credentials: 'same-origin',
                body: JSON.stringify({ message: text }),
            });
            const data = await response.json().catch(() => ({}));
            waiting.remove();

            if (!response.ok) {
                this.bubble(data.message || this.$root.dataset.failed, 'error');
                return;
            }

            const answer = this.bubble(data.reply, 'assistant');
            if (data.html) answer.insertAdjacentHTML('beforeend', data.html);
        } catch {
            waiting.remove();
            this.bubble(this.$root.dataset.failed, 'error');
        } finally {
            this.busy = false;
            this.$refs.input.focus();
        }
    },

    async restart() {
        try {
            await fetch(this.$root.dataset.forget, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
                credentials: 'same-origin',
            });
        } catch {
            // Nothing to undo: the next question simply starts with what is left.
        }
        [...this.$refs.log.children].slice(1).forEach((node) => node.remove());
        this.$refs.suggestions?.closest('div')?.classList.remove('hidden');
        this.$refs.input.focus();
    },

    bubble(text, who) {
        const box = document.createElement('div');
        const tone = {
            user: 'justify-self-end max-w-[85%] px-3.5 py-2.5 bg-ink text-paper',
            assistant: 'justify-self-start w-full max-w-full',
            error: 'justify-self-start max-w-[85%] px-3.5 py-2.5 bg-danger-wash text-danger',
        }[who];
        box.className = `grid motion-safe:animate-fade-up ${tone}`;

        const p = document.createElement('p');
        p.className = 'm-0 text-sm leading-body whitespace-pre-line [overflow-wrap:anywhere]';
        p.dir = 'auto';
        p.textContent = text;
        box.append(p);

        this.$refs.log.append(box);
        this.$refs.log.scrollTop = this.$refs.log.scrollHeight;

        return box;
    },

    /** Three dots while the answer is on its way, with words for a screen reader. */
    thinking() {
        const box = document.createElement('div');
        box.className = 'justify-self-start inline-flex items-center gap-1.5 px-3.5 py-3 bg-paper-2';
        box.setAttribute('role', 'status');
        for (let i = 0; i < 3; i++) {
            const dot = document.createElement('span');
            dot.className = 'size-1.5 rounded-pill bg-muted motion-safe:animate-pulse';
            dot.setAttribute('aria-hidden', 'true');
            box.append(dot);
        }
        const words = document.createElement('span');
        words.className = 'sr-only';
        words.textContent = this.$root.dataset.thinking;
        box.append(words);
        this.$refs.log.append(box);
        this.$refs.log.scrollTop = this.$refs.log.scrollHeight;

        return box;
    },
});
