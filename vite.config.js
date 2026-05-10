import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import legacy from '@vitejs/plugin-legacy';

const legacyBrowserTargets = [
    'defaults',
    'not IE 11',
    'not op_mini all',
    'safari >= 14',
    'ios_saf >= 14',
    'firefox >= 91',
    'chrome >= 90',
    'edge >= 90',
    'android >= 90',
    'samsung >= 15',
];

export default defineConfig({
    plugins: [
        laravel({
            publicDirectory: '.',
            hotFile: 'hot',
            input: ['resources/css/app.css', 'resources/scss/app.scss', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
        legacy({
            modernPolyfills: true,
            renderLegacyChunks: true,
            targets: legacyBrowserTargets,
        }),
    ],
    build: {
        cssTarget: ['chrome90', 'edge90', 'firefox91', 'safari14'],
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
