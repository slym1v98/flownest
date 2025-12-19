import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import typography from '@tailwindcss/typography';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss({
            plugins: [typography],
        }),
        wayfinder({
            formVariants: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        // Production optimizations
        target: 'es2020',
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
                drop_debugger: true,
            },
        },
        // Code splitting for better caching
        rollupOptions: {
            output: {
                manualChunks: {
                    'vue-vendor': ['vue', '@inertiajs/vue3'],
                    'editor': ['@tiptap/vue-3', '@tiptap/starter-kit'],
                    'ui': ['reka-ui', 'lucide-vue-next'],
                },
            },
        },
        // Asset optimization
        assetsInlineLimit: 4096,
        chunkSizeWarningLimit: 1000,
        // Source maps for production debugging (can be disabled for smaller builds)
        sourcemap: process.env.VITE_SOURCEMAP === 'true',
    },
    // CSS optimization
    css: {
        devSourcemap: true,
    },
    // Server configuration
    server: {
        hmr: {
            host: 'localhost',
        },
    },
});
