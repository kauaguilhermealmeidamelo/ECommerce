<!-- src/components/dashboard/StatCard.vue -->
<template>
  <v-card class="stat-card" elevation="0">
    <div class="stat-header">
      <div :class="['stat-icon', variant]">
        <slot name="icon" />
      </div>
      <slot name="badge" />
    </div>

    <div class="stat-body">
      <h3 class="stat-value">{{ value }}</h3>
      <p class="stat-title">{{ title }}</p>
    </div>

    <div v-if="comparison" class="stat-footer">
      {{ comparison }}
    </div>
  </v-card>
</template>

<script setup lang="ts">
defineProps({
  title: { type: String, required: true },
  value: { type: [String, Number], required: true },
  comparison: { type: String, default: '' },
  variant: { type: String, default: 'indigo' } // 'indigo', 'purple', 'amber', 'emerald'
})
</script>

<style scoped>
.stat-card {
  background-color: #ffffff;
  padding: 1.5rem;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
  border: 1px solid #f3f4f6;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
  transition: box-shadow 0.2s ease;
}

.stat-card:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
}

.stat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.stat-icon {
  padding: 0.75rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon.indigo {
  background-color: #eef2ff;
  color: #4f46e5;
}

.stat-icon.purple {
  background-color: #f3e8ff;
  color: #9333ea;
}

.stat-icon.amber {
  background-color: #fef3c7;
  color: #d97706;
}

.stat-icon.emerald {
  background-color: #ecfdf5;
  color: #059669;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
  letter-spacing: -0.025em;
}

.stat-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
  margin-top: 0.25rem;
}

.stat-footer {
  font-size: 0.75rem;
  color: #9ca3af;
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #f9fafb;
}
</style>