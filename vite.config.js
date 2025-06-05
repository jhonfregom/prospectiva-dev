import { defineConfig } from 'vite';
import { nodePolyfills } from 'vite-plugin-node-polyfills';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
                input: [
                    'resources/js/app.js',
                ],
                refresh: true
        }),
        vue(),
        nodePolyfills(),//NodeJs process, Fix to error "Uncaught ReferenceError: process is not defined"
    ],
    css:{
        preprocessorOptions: {
            scss: {
                // Add global scss variables, This will be available in all vue components
                // additionalData: '@import "@/../sass/variables.scss";',
            },
        },
    },
    resolve: {
        alias: {
           //'@': path.resolve(__dirname, 'resources/js'),
            'vue': 'vue/dist/vue.esm-bundler.js',
        },
    },
    //Development server (Local)
    server: {
        // For open in broser defined,
        // Require in .env BROWSER="firefox-developer-edition" at linux and;
        // BROWSER="Firefox Developer Edition" at MacOs
        host: 'prospectiva.com',
        open: true, //Auto open in browser
        port: 5173,

    }
});
