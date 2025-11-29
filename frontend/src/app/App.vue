<template>
  <div class="main-container">
    <Header />
    <main :class="{ 'with-sidebar': true, 'sidebar-collapsed': sidebarCollapsed }">
      <router-view />
    </main>
  </div>
</template>

<script lang="ts" setup>
import { inject, type Ref } from "vue";
import Header from "../shared/ui/organism/Header.vue";

const sidebarCollapsed = inject<Ref<boolean>>('sidebarCollapsed', { value: false } as Ref<boolean>);
</script>

