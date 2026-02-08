<script setup lang="js">
</script>

<template>

  <div class="layout">

    <div class="mx-auto text-2xl">
      <h1>Настройки</h1>
    </div>

    <nav class="nav whitespace-nowrap">
      <router-link to="/">Главная</router-link>
      <router-link to="/test-1">Test 1</router-link>
      <router-link to="/test-2">Test 2</router-link>
    </nav>

    <main class="content">
      <router-view />
    </main>

    <footer class="footer">
      Bitrix24 приложение
    </footer>

  </div>
</template>

<style scoped>
.layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.nav {
  display: flex;
  gap: 20px;
  padding: 15px;
  background: #f0f0f0;
}

.content {
  flex: 1;
  padding: 20px;
}

.footer {
  padding: 15px;
  background: #f0f0f0;
  text-align: center;
}
</style>
