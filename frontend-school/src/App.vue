<script setup lang="ts">
import { RouterView } from 'vue-router'
import { onMounted } from 'vue'


onMounted(() => {
  // 检查用户登录状态
})
</script>

<template>
  <div id="app">
    <RouterView />
  </div>
</template>

<style>
#app {
  font-family: 'Helvetica Neue', Helvetica, 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', '微软雅黑', Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
  margin: 0;
  padding: 0;
  height: 100vh;
  width: 100vw;
  overflow-x: hidden;
}

* {
  box-sizing: border-box;
}

body, html {
  margin: 0;
  padding: 0;
  background-color: #f5f5f5;
  width: 100vw;
  height: 100vh;
  overflow-x: hidden;
}
</style>
