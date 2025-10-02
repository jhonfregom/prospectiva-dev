import { defineConfig, loadEnv } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import { nodePolyfills } from 'vite-plugin-node-polyfills'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  // Lee prefijo desde el entorno. En prod será "/app"
  let base = env.ASSET_URL || env.VITE_BASE || '/'
  if (!base.endsWith('/')) base += '/'

  return {
    base,
    plugins: [
      laravel({ input: ['resources/js/app.js'], refresh: true }),
      vue(),
      nodePolyfills(),
    ],
    resolve: {
      alias: {
        'vue': 'vue/dist/vue.esm-bundler.js',
      },
    },
    // Para local, evita forzar 'prospectiva.com' salvo que lo tengas en hosts
    server: {
      host: true,            // o '127.0.0.1'
      port: 5173,
      strictPort: true,
      hmr: { host: 'localhost', port: 5173 }, // ajusta si usas otro host local
    },
  }
})
