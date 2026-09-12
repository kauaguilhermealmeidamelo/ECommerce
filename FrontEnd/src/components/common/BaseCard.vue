<!-- src/components/common/BaseCard.vue -->
<template>
  <v-card class="base-card" elevation="0">
    <slot />
  </v-card>
</template>

<script setup>
// Componente base reutilizável para containers do painel
</script>

<style scoped>
.base-card {
  background-color: #ffffff;
  border-radius: 1rem;
  border: 1px solid #f3f4f6;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}
</style>