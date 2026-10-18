import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// The port published on the host, which can differ from the one Vite listens on in the container.
const hostPort = Number(process.env.VITE_PORT ?? 5173);

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // Reachable from the host through the published port; the browser connects to localhost.
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        origin: `http://localhost:${hostPort}`,
        hmr: {
            host: 'localhost',
            clientPort: hostPort,
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
