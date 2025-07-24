<template>
  <MainLayout>
    <router-view />
  </MainLayout>
</template>

<script>
import MainLayout from './MainLayout.vue'

export default {
  name: 'AuthLayout',
  components: {
    MainLayout
  }
}
</script> 