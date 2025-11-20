<template>
  <div class="main-contaner">
    <Header />
    <main>
      <router-view />
    </main>
  </div>
</template>

<script lang="ts">
import Header from "./components/header/Header.vue";
export default {
  components: { Header },
};
</script>
