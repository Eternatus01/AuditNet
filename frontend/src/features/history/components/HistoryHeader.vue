<template>
  <div class="history-header">
    <h1>{{ title }}</h1>
    <p v-if="subtitle" class="history-subtitle">{{ subtitle }}</p>
  </div>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    title?: string;
    subtitle?: string;
  }>(),
  {
    title: "История аудитов",
    subtitle: "Просмотр всех проведённых аудитов",
  }
);
</script>

<style scoped>
.history-header {
  text-align: center;
  margin-bottom: 3rem;
}

.history-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  background: linear-gradient(135deg, #646cff 0%, #535bf2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.history-subtitle {
  color: rgba(255, 255, 255, 0.6);
  font-size: 1.1rem;
}
</style>

