<script setup lang="ts">
import { RouterView } from 'vue-router';
import { onMounted } from 'vue';
import Navbar from './components/Navbar.vue';
import Footer from './components/Footer.vue';
import ActiveRecord from './components/ActiveRecord.vue';
import { useRecordStore } from './stores/record';
import { useAuthStore } from './stores/auth';

const recordStore = useRecordStore();
const authStore = useAuthStore();

onMounted(() => {
  setTimeout(() => {
    // Vérifier si l'utilisateur est authentifié avant de chercher l'enregistrement actif
    if (authStore.isAuthenticated) {
      recordStore.checkActiveRecord();
    }
  }, 2000);
});
</script>

<template>
  <div id="app">
    <Navbar />
    <main>
      <RouterView />
      <ActiveRecord
      v-if="recordStore.activeRecord"
      :kid-name="recordStore.activeRecord.kid?.first_name || ''"
    />
    </main>
    <footer>
      <Footer />
    </footer>
  </div>
</template>

<style scoped>
#app {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  padding-top: 60px;
}

main {
  flex: 1;
}

footer {
  text-align: center;
}
</style>