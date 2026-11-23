import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/Enums/**/*.php',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Inter Variable"', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#eef6ff',
                    100: '#d9eaff',
                    200: '#bcdaff',
                    300: '#8ec3ff',
                    400: '#59a1ff',
                    500: '#337dfc',
                    600: '#1c5ef1',
                    700: '#1549dd',
                    800: '#183cb3',
                    900: '#1a378d',
                    950: '#142356',
                },
            },
            boxShadow: {
                card: '0 1px 2px 0 rgb(15 23 42 / 0.04), 0 1px 3px 0 rgb(15 23 42 / 0.06)',
            },
        },
    },
    plugins: [forms],
};
