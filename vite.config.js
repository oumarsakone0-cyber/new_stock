import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    proxy: {
      // En dev, les appels à /api/* sont redirigés vers le serveur distant (évite CORS)
      '/api': {
        target: 'https://www.aliadjame.com',
        changeOrigin: true,
        secure: true,
        ws: false,
        rewrite: (path) => {
          // Garder le chemin tel quel pour éviter les doubles redirections
          return path;
        },
      },
    },
  },
})
