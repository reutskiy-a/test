<script setup lang="js">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import MainLayout from './layouts/MainLayout.vue'
import EmptyLayout from './layouts/EmptyLayout.vue'

const route = useRoute()

const layout = computed(() =>
    route.meta.layout === 'empty' ? EmptyLayout : MainLayout
)
</script>

<template>
  <component :is="layout">
    <router-view />
  </component>
</template>

