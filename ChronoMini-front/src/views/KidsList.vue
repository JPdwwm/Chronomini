<script setup lang="ts">
import { ref,onMounted } from 'vue';
import { getKids } from '@/_services/KidService';
import LoadingIcon from '@/components/LoadingIcon.vue'; 
import {formatDate} from '@/_utils/dateUtils'
import type {Kid} from '@/_models/Kid'


const kids = ref<Kid[]>([]); 
const isLoading = ref<boolean>(true); 

// Fonction pour charger les enfants
const loadKids = async () => {
  isLoading.value = true; 
  try {
    kids.value = await getKids(); 
  } catch (error) {
  }finally {
    isLoading.value = false; // Fin du chargement
  }
};

onMounted(() => {
  loadKids();
});
</script>

<template>
  <div class="page-container">
    <h1>Mes Enfants</h1>
    <LoadingIcon v-if="isLoading" />
    <div class="kids-container" v-else>
      <template v-if="kids.length > 0">
        <div v-for="kid in kids" :key="kid.id" class="card">
          <div class="card-details">
            <h3 class="text-title truncate-text" :title="kid.first_name">
              {{ kid.first_name }}
            </h3>
            <p class="text-body"><strong>Né(e) le:</strong> {{ formatDate(kid.birth_date) }}</p>
          </div>
          <router-link :to="`/kids/${kid.id}`" class="card-button">Gestion</router-link>
        </div>
      </template>
      <router-link to="/kids/add" class="card add-card">
        <div class="card-details">
          <font-awesome-icon 
            icon="fa-solid fa-plus" 
            class="add-icon"
          />
          <h3 class="text-title">Ajouter un enfant</h3>
        </div>
      </router-link>
    </div>
  </div>
</template>

<style scoped>
.page-container {
  text-align: center;
}

.kids-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
  padding: 20px;
  font-family: Georgia, 'Times New Roman', Times, serif;
  text-align: center;
}

.card {
  width: 190px;
  height: 14rem;
  border-radius: 20px;
  background: #f5f5f5;
  position: relative;
  padding: 1.8rem;
  border: 2px solid #c3c6ce;
  transition: 0.5s ease-out;
  word-break: keep-all;
  overflow: visible; 
  margin: 0 auto; /* Aide au centrage */
}

.card-details {
  color: black;
  height: 100%;
  gap: 0.5em;
  display: grid;
  place-content: center;
  overflow: hidden; /* Ajout de overflow: hidden */
}

.card-button {
  transform: translate(-50%, 125%);
  width: 60%;
  border-radius: 1rem;
  border: none;
  background-color: #78BB99;
  color: #fff;
  font-size: 1rem;
  padding: 0.5rem 1rem;
  position: absolute;
  left: 50%;
  bottom: 0;
  opacity: 0;
  transition: 0.3s ease-out;
  text-align: center;
}

.text-body {
  color: rgb(134, 134, 134);
}

.text-title {
  font-size: 1.5em;
  font-weight: bold;
}

/* Hover Effects */
.card:hover {
  border-color: #358E9D;
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.25);
}

.card:hover .card-button {
  transform: translate(-50%, 50%);
  opacity: 1;
}

.add-card {
  text-decoration: none;
  border: 2px dashed #c3c6ce;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  color: inherit;
}

.add-card .card-details {
  text-align: center;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 15px; /* Espace entre l'icône et le texte */
}

.add-icon {
  font-size: 2em;
  color: #008bf8;
  display: block; /* Assure que l'icône se comporte comme un bloc */
}

.add-card:hover {
  border-color: #008bf8;
  background-color: #f8f9fa;
}

.add-card .text-title {
  color: #666;
}

.add-card:hover .text-title {
  color: #008bf8;
}

.truncate-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}

.card-details {
  color: black;
  height: 100%;
  gap: 0.5em;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  overflow: hidden;
}

.text-title {
  font-size: 1.5em;
  font-weight: bold;
  width: 100%;
  margin-bottom: 5px;
}

.text-body {
  color: rgb(134, 134, 134);
  width: 100%;
  margin-top: auto; 
}


@media (max-width: 768px) {
  .kids-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }
  
  .card {
    width: 80%; 
    max-width: 250px; 
    margin-bottom: 20px; 
  }

  .card-button {
    opacity: 1;
    transform: translate(-50%, 50%);
    width: 70%;
    font-size: 0.9rem;
  }
}
</style>