import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    publicDir: false,
    plugins: [
        laravel({
            input: ['resources/js/app.jsx', 'resources/css/filament/skillup/theme.css'],
            refresh: true,
        }),
        react(),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    optimizeDeps: {
        exclude: ['lucide-react'],
    },
});
