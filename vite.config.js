import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Tailwind, the tokens and the fonts.
                'resources/css/app.css',
                // Alpine, its components, and the plain modules.
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // An IPv4 address rather than Node's default, which on Windows is
        // often [::1]. The CSP must list the dev server's origin, and CSP has
        // no syntax for an IPv6 literal — see ContentSecurityPolicy::viteDevServer.
        host: '127.0.0.1',
    },
});
