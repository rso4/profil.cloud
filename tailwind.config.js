import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Warna dinamis tenant — nilai di-resolve dari CSS variables
                // yang disuntikkan per-tenant di layout (lihat base.blade.php).
                primary: 'rgb(var(--primary-rgb))',
                secondary: 'var(--secondary-color)',
            },
        },
    },
    plugins: [],
};
