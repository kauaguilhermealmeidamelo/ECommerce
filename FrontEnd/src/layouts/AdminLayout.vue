<template>
  <div class="layout">
    <router-view />
    <BottomNav />
  </div>
</template>

<script setup>
import BottomNav from '@/components/BottomNav.vue'
</script>

<style scoped>
.layout {
  max-width: 480px;
  margin: 0 auto;
}
</style>
