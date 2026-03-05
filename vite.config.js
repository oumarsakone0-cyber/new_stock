import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    proxy: {
      // En dev, les appels à /api/* sont proxifiés vers le serveur distant
      '/api': {
        target: 'https://www.aliadjame.com',
        changeOrigin: true,
        secure: true,
        ws: false,
      },
    },
  },
})
