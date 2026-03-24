<script setup lang="ts">
import { ref } from 'vue';
import { createKids } from '@/_services/KidService'; // Import de la fonction pour créer un enfant
import type { Kid } from '@/_models/Kid';
import router from '@/router';

// Initialisation des données du formulaire
const kid = ref<Kid>({
  first_name: '',
  birth_date: ''
});

// Messages d'erreur et de succès
const errorMessage = ref<string | null>(null);
const successMessage = ref<string | null>(null);

// Fonction pour créer un enfant
async function createKid() {
  try {
    // Appel à la fonction `createKids` du service
    await createKids(kid.value);

    // Afficher un message de succès si tout se passe bien
    successMessage.value = 'Enfant ajouté avec succès !';
    errorMessage.value = null;
    router.push('/kids');
  } catch (error: any) {
    // Gérer les erreurs et afficher un message d'erreur
    errorMessage.value = error.response?.data?.message || 'Une erreur est survenue.';
    successMessage.value = null;
  }
}
</script>

<template>
  <div class="page-container"> 
    <div class="form-container">
      <h1>Ajouter un enfant</h1>
        <div v-if="errorMessage" class="error">{{ errorMessage }}</div>
        <div v-if="successMessage" class="success">{{ successMessage }}</div>
    <form @submit.prevent="createKid">
      <div class="form-group">
        <label for="first_name">Prénom de l'enfant</label>
        <input
          id="first_name"
          v-model="kid.first_name"
          type="text"
          placeholder="Entrez le prénom"
          required
        />
      </div>

      <div class="form-group">
        <label for="birth_date">Date de naissance</label>
        <input
          id="birth_date"
          class="custom-form"
          v-model="kid.birth_date"
          type="date"
          required
        />
      </div>

      <div class="form-group">
        <button type="submit" class="submit-button">Ajouter</button>
      </div>
    </form>
  </div>
</div>
</template>

<style scoped>

.page-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
}

.form-container {
  max-width: 400px;
  margin: 0 auto;
  padding: 20px;
  background-color: #ffffff;
  border-radius: 8px;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
  font-family: Arial, sans-serif;
}

h1 {
  text-align: center;
  font-size: 24px;
  color: #333;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  font-size: 14px;
  color: #555;
  margin-bottom: 5px;
  display: block;
}

.form-group input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 14px;
  color: #333;
}

.form-group input:focus {
  border-color: #66afe9;
  outline: none;
}

.error,
.success {
  font-size: 14px;
  padding: 10px;
  text-align: center;
  margin-bottom: 15px;
  border-radius: 4px;
}

.error {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.submit-button {
  width: 100%;
  padding: 12px;
  background-color: #358E9D;
  color: #fff;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
}

.submit-button:hover {
  background-color: #0056b3;
}
</style>
