<template>
  <div class="unauthorized-container">
    <div class="unauthorized-card">
      <h1>Accès refusé</h1>
      <div class="icon-container">
        <font-awesome-icon icon="fa-solid fa-lock" class="lock-icon" />
      </div>
      <p>Vous n'avez pas les autorisations nécessaires pour accéder à cette section.</p>
      <p>Cette section est réservée aux administrateurs.</p>
      <router-link to="/" class="home-button">
        Retourner à l'accueil
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
</script>

<style scoped>
.unauthorized-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f5f5f5;
  padding: 2rem;
}

.unauthorized-card {
  background: white;
  border-radius: 10px;
  padding: 3rem;
  max-width: 500px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

h1 {
  color: #E26D5C;
  margin-bottom: 1.5rem;
}

.icon-container {
  margin: 2rem 0;
}

.lock-icon {
  font-size: 5rem;
  color: #E26D5C;
}

p {
  color: #666;
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.home-button {
  display: inline-block;
  background: #358E9D;
  color: white;
  padding: 0.8rem 1.5rem;
  border-radius: 6px;
  text-decoration: none;
  margin-top: 2rem;
  transition: background 0.2s;
}

.home-button:hover {
  background: #2d7a87;
}
</style>