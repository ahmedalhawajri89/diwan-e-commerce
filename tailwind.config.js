import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                main: '#0f172a',
                primary: '#1e293b',
                accent: '#ca8a04',
                soft: '#f8fafc',
                surface: '#ffffff',
                muted: '#64748b',
            }
        },
    },

    plugins: [forms],
};
