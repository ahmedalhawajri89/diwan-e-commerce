// The home page's fit demo: drag two walls, watch one real piece answer.
//
// It runs the same assessment as App\Support\Fit::assess, in the browser, so
// the answer changes as the slider moves rather than after a round trip. The
// thresholds are not copied here — the server writes them into `data-fit`
// from Fit::rules(), so the two cannot disagree about what "tight" means.
//
// The sliders are real form fields named `width` and `length`: submitting the
// form saves them as the visitor's room, exactly as the room dialog does.
//
//   <form x-data="fitDemo" data-fit='{"piece":…,"rules":…,"labels":…,"room":…}'>

export default () => ({
    width: 350,
    length: 480,
    piece: { w: 0, d: 0, h: 0 },
    rules: {},
    labels: {},

    init() {
        const config = JSON.parse(this.$el.dataset.fit);

        this.piece = config.piece;
        this.rules = config.rules;
        this.labels = config.labels;
        this.width = config.room.width;
        this.length = config.room.length;
    },

    /** Mirrors Fit::assess. Keep the order of the checks identical. */
    get assessment() {
        const { w, d, h } = this.piece;
        const roomW = Number(this.width);
        const roomL = Number(this.length);

        const share = (w * d) / (roomW * roomL);
        const longSide = Math.max(w, d);
        const shortSide = Math.min(w, d);
        const longWall = Math.max(roomW, roomL);
        const shortWall = Math.min(roomW, roomL);

        if (longSide > longWall || shortSide > shortWall) return { status: 'no', note: 'too_long', share };
        if (share > this.rules.dominates) return { status: 'no', note: 'dominates', share };
        if (shortWall - shortSide < this.rules.walkway) return { status: 'tight', note: 'walkway', share };
        if (share > this.rules.crowded) return { status: 'tight', note: 'crowded', share };
        if (Math.min(w, d, h || Infinity) > this.rules.doorway) return { status: 'tight', note: 'doorway', share };

        return { status: 'fits', note: null, share };
    },

    get verdict() {
        return this.labels[this.assessment.status];
    },

    get reason() {
        const { note } = this.assessment;

        return note ? this.labels.notes[note] : this.labels.fitsExplain;
    },

    get shareText() {
        return this.labels.share.replace(':percent', (this.assessment.share * 100).toFixed(1));
    },

    get floorText() {
        return ((this.width * this.length) / 10000).toFixed(1);
    },

    /* ── The plan drawing, in centimetres ── */

    get viewBox() {
        return `0 0 ${this.width} ${this.length}`;
    },

    /** The piece stands against the longest wall, centred on it. */
    get along() {
        return Number(this.width) >= Number(this.length);
    },

    get pieceW() {
        return this.along ? Math.max(this.piece.w, this.piece.d) : Math.min(this.piece.w, this.piece.d);
    },

    get pieceH() {
        return this.along ? Math.min(this.piece.w, this.piece.d) : Math.max(this.piece.w, this.piece.d);
    },

    get pieceX() {
        return this.along ? (this.width - this.pieceW) / 2 : 0;
    },

    get pieceY() {
        return this.along ? this.length - this.pieceH : (this.length - this.pieceH) / 2;
    },

    /** The walkway the rules ask for, drawn in front of the piece. */
    get walkX() {
        return this.along ? this.pieceX : this.pieceW;
    },

    get walkY() {
        return this.along ? Math.max(0, this.pieceY - this.rules.walkway) : this.pieceY;
    },

    get walkW() {
        return this.along ? this.pieceW : this.rules.walkway;
    },

    get walkH() {
        return this.along ? Math.min(this.rules.walkway, this.pieceY) : this.pieceH;
    },

    /* ── Colour follows the verdict; the verdict is always also a word ── */

    get pieceClass() {
        return {
            fits: 'fill-ok-wash stroke-ok',
            tight: 'fill-warn-wash stroke-warn',
            no: 'fill-danger-wash stroke-danger',
        }[this.assessment.status];
    },

    get verdictClass() {
        return {
            fits: 'text-ok',
            tight: 'text-warn',
            no: 'text-danger',
        }[this.assessment.status];
    },
});
