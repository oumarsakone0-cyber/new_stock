import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

const API_TARGET = 'https://www.aliadjame.com'

// Proxy qui suit les redirections côté serveur pour éviter CORS quand le backend renvoie un 302
function proxyApiPlugin() {
  return {
    name: 'proxy-api-follow-redirects',
    configureServer(server) {
      server.middlewares.use(async (req, res, next) => {
        if (!req.url?.startsWith('/api/')) {
          return next()
        }
        const url = new URL(req.url, `http://${req.headers.host}`)
        const targetUrl = `${API_TARGET}${url.pathname}${url.search}`
        const headers = { ...req.headers }
        delete headers.host
        headers.host = new URL(API_TARGET).host
        try {
          const hasBody = ['POST', 'PUT', 'PATCH'].includes(req.method)
          const body = hasBody ? await readRawBody(req) : undefined
          const response = await fetch(targetUrl, {
            method: req.method,
            headers,
            body: body?.length ? body : undefined,
            redirect: 'follow',
          })
          res.statusCode = response.status
          const skipHeaders = ['transfer-encoding', 'content-encoding', 'content-length']
          response.headers.forEach((value, key) => {
            if (!skipHeaders.includes(key.toLowerCase())) res.setHeader(key, value)
          })
          res.setHeader('Access-Control-Allow-Origin', req.headers.origin || '*')
          res.setHeader('Access-Control-Allow-Credentials', 'true')
          const buf = Buffer.from(await response.arrayBuffer())
          res.setHeader('Content-Length', buf.length)
          res.end(buf)
        } catch (err) {
          console.error('[proxy-api]', err.message)
          res.statusCode = 502
          res.setHeader('Content-Type', 'application/json')
          res.end(JSON.stringify({ success: false, error: 'Proxy error: ' + err.message }))
        }
      })
    },
  }
}

function readRawBody(req) {
  return new Promise((resolve, reject) => {
    const chunks = []
    req.on('data', (chunk) => chunks.push(chunk))
    req.on('end', () => resolve(Buffer.concat(chunks)))
    req.on('error', reject)
  })
}

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    proxyApiPlugin(),
  ],
  server: {
    // Pas de proxy /api ici : proxyApiPlugin suit les redirections côté serveur pour éviter CORS
  },
})
