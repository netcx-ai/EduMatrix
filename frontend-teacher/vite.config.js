import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig(({ mode }) => {
  // 读取 .env
  const env = loadEnv(mode, process.cwd())

  return {
    plugins: [vue()],
    resolve: {
      alias: { '@': path.resolve(__dirname, 'src') }
    },
    server: {
      proxy: {
        [env.VITE_API_BASE || '/api']: {
          target: env.VITE_API_TARGET,
          changeOrigin: true,
          secure: false,
          ws: true
        }
      }
    }
  }
})