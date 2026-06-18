import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '192.168.200.6',
        port: 5173,
        strictPort: true,
        cors: {
            origin: '*', //WAJIB TAMBAH INI
        },
        hmr: {
            host: '192.168.200.6',
        },
    },
});