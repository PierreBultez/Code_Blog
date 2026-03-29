import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/public.css', 'resources/js/app.js', 'resources/js/public.js'],
            refresh: true,
        }),
        tailwindcss(),
        // Patch Alpine CSP build: its parser enumerates all globalThis properties,
        // which triggers Chrome's "Shared Storage API deprecated" warning when
        // accessing window.sharedStorage. Add it to the skip list alongside styleMedia.
        {
            name: 'patch-alpine-csp-globals',
            transform(code, id) {
                if (id.includes('@alpinejs/csp') || id.includes('@alpinejs_csp')) {
                    return code.replace(
                        'if (key === "styleMedia")',
                        'if (key === "styleMedia" || key === "sharedStorage")'
                    );
                }
            },
        },
    ],
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
