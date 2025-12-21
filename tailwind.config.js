import forms from '@tailwindcss/forms';

// Palette diambil dari warna utama logo (ungu-magenta) untuk dipakai sebagai primary dan override indigo.
const brandColors = {
    50: '#faf5fb',
    100: '#f3e6f7',
    200: '#e4c7ef',
    300: '#d4a7e6',
    400: '#c07bd7',
    500: '#a851c4',
    600: '#9137ae',
    700: '#7b2d92',
    800: '#672575',
    900: '#541d5e',
    DEFAULT: '#7b2d92',
};

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{js,jsx,ts,tsx}',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Roboto', 'sans-serif'],
            },
            colors: {
                primary: brandColors,
                indigo: brandColors, // override semua kelas indigo agar mengikuti brand
            },
        },
    },

    plugins: [forms],
};
