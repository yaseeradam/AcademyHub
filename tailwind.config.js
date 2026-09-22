import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                },
                dark: {
                    50: '#1a1a1a',
                    100: '#2d2d2d',
                    200: '#404040',
                    300: '#525252',
                    400: '#666666',
                    500: '#808080',
                    600: '#999999',
                    700: '#b3b3b3',
                    800: '#cccccc',
                    900: '#e6e6e6',
                }
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            animation: {
                'ripple': 'ripple 0.6s linear',
                'shimmer': 'shimmer 2s infinite',
            },
            keyframes: {
                ripple: {
                    '0%': { transform: 'scale(0)', opacity: 1 },
                    '100%': { transform: 'scale(4)', opacity: 0 },
                },
                shimmer: {
                    '0%': { backgroundPosition: '-1000px 0' },
                    '100%': { backgroundPosition: '1000px 0' },
                }
            }
        },
    },
    plugins: [],
};
