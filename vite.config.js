import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Hallmark token layer — the single source of truth for every
                // colour, font, space, and easing. Must load before the bundles
                // that reference it. See design.md.
                'tokens.css',
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/scss/furnish/style.scss',
                'resources/js/furnish/main.js'
            ],
            refresh: true,
        }),
    ],
});
