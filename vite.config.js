import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/sass/app.scss', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        // Bind to all interfaces so the dockerized "node" service is reachable.
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        // HMR connects from the browser via the published host port.
        hmr: {
            host: 'localhost',
        },
        // Polling is required for file-change detection on Windows/Docker bind
        // mounts, but polling the WHOLE project (vendor, node_modules, storage,
        // git, ...) pegs the CPU (~70%) and starves the PHP container, making
        // every page load slow. Restrict polling to the source folders we
        // actually edit and explicitly ignore the heavy/generated trees.
        watch: {
            usePolling: true,
            interval: 300,
            ignored: [
                '**/.git/**',
                '**/vendor/**',
                '**/node_modules/**',
                '**/storage/**',
                '**/bootstrap/cache/**',
                '**/public/build/**',
                '**/tests/**',
                '**/database/**',
            ],
        },
    },
});
