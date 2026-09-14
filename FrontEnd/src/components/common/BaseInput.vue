<!-- src/components/common/BaseInput.vue -->
<template>
  <v-text-field
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    :label="label"
    :type="type"
    :placeholder="placeholder"
    variant="outlined"
    density="comfortable"
    hide-details="auto"
    class="base-input"
  />
</template>

<script setup lang="ts">
defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' }
})

defineEmits(['update:modelValue'])
</script>

<style scoped>
.base-input :deep(.v-field) {
  border-radius: 0.75rem !important;
  background-color: #ffffff;
}
</style>