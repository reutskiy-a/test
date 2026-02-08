import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import bitrix24UIPluginVite  from '@bitrix24/b24ui-nuxt/vite'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    bitrix24UIPluginVite ({})
  ],
  server: {
    proxy: {
      '/api': {
        target: 'https://bitrix24.reutskiy-a.ru',
        changeOrigin: true,
        secure: false,
      }
    }
  }
})
