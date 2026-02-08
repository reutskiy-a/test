import './assets/css/main.css'

import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import b24UiPlugin from '@bitrix24/b24ui-nuxt/vue-plugin'

import App from './App.vue'

const app = createApp(App)

const basePath = import.meta.env.BASE_URL || '/'

app.use(createRouter({

    routes: [
        { path: '/', component: () => import('./pages/index.vue') },
        { path: '/test-1', component: () => import('./pages/test-1.vue') },
        { path: '/test-2', component: () => import('./pages/test-2.vue') },

        {
            path: '/placement',
            component: () => import('./pages/placement.vue'),
            meta: { layout: 'empty' }
        }
    ],
  history: createWebHistory(basePath)
}))

app.use(b24UiPlugin)

app.mount('#app')
