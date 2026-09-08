import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

/**
 * Frontend CSS/JS now live in public/assets (Laravel asset()).
 * Vite is unused for this project.
 */
export default defineConfig({
    plugins: [
        laravel({
            input: [],
            refresh: ['resources/views/**'],
        }),
    ],
});
