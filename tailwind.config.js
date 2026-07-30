import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: {
            colors: {
                skillup: {
                    blue: '#0D4EFF',
                    navy: '#14183E',
                    deep: '#1E3A8A',
                    soft: '#EFF6FF',
                    light: '#DBEAFE',
                    orange: '#F97316',
                    ink: '#101828',
                    muted: '#667085',
                },
            },
            fontFamily: {
                sans: ['Jost', ...defaultTheme.fontFamily.sans],
                montserrat: ['Montserrat', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                // Reusable elevation scale — use these instead of ad-hoc shadow values.
                card: '0 1px 2px 0 rgb(16 24 40 / 0.04), 0 1px 3px 0 rgb(16 24 40 / 0.07)',
                'card-hover': '0 16px 32px -12px rgb(16 24 40 / 0.20)',
                elevated: '0 24px 48px -16px rgb(20 24 62 / 0.30)',
                focus: '0 0 0 3px rgb(13 78 255 / 0.35)',
            },
            transitionTimingFunction: {
                premium: 'cubic-bezier(0.16, 1, 0.3, 1)',
            },
            maxWidth: {
                headline: '42rem', // ~672px — keeps hero/section headings readable.
            },
            keyframes: {
                marquee: {
                    '0%': { transform: 'translateX(0)' },
                    '100%': { transform: 'translateX(-50%)' },
                },
                'fade-in-up': {
                    '0%': { opacity: '0', transform: 'translateY(12px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
            animation: {
                marquee: 'marquee 24s linear infinite',
                'fade-in-up': 'fade-in-up 0.4s cubic-bezier(0.16, 1, 0.3, 1) both',
            },
        },
    },

    plugins: [forms],
};
