<script setup lang="ts">
import { ref } from 'vue';
import type { Kid } from '@/_models/Kid.ts';

const kid = ref<Kid>({
  first_name: '',
  birth_date: ''
});

// Props
const props = defineProps<{
  isVisible: boolean;
  partner: { first_name?: string; last_name?: string; id?: number } | null;
  kids: Kid[];
  errorMessage?: string; // Nouvelle prop
}>();

// Émetteurs
const emit = defineEmits(['close', 'share']);

// État local
const selectedKidId = ref<number | null>(null);

// Méthodes
const handleClose = () => {
  selectedKidId.value = null;
  emit('close');
};

const handleShare = () => {
  if (selectedKidId.value) {
    emit('share', selectedKidId.value);
    selectedKidId.value = null;
  }
};
</script>

<template>
  <div v-if="isVisible" class="modal-overlay">
    <div class="modal-container">
      <div class="modal-header">
        <h2>Lier un enfant avec {{ partner?.first_name }} {{ partner?.last_name }}</h2>
        <button @click="handleClose" class="close-button">&times;</button>
      </div>
      
      <div class="modal-body">
        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>
        <div v-if="kids.length === 0" class="no-kids-message">
          <p>Vous n'avez pas d'enfants à partager.</p>
        </div>
        <div v-else>
          <p class="modal-description">
            Sélectionnez l'enfant que vous souhaitez lier avec ce partenaire :
          </p>
          
          <div class="kids-list">
            <div 
              v-for="kid in kids" 
              :key="kid.id"
              class="kid-item"
              :class="{ selected: selectedKidId === kid.id }"
              >
              <div class="kid-info">
                <h3>{{ kid.first_name }}</h3>
                <p v-if="kid.birth_date">Né(e) le {{ new Date(kid.birth_date).toLocaleDateString() }}</p>
              </div>
              <div class="kid-selector">
                <input 
                  type="radio" 
                  :id="`kid-${kid.id}`" 
                  name="selectedKid" 
                  :value="kid.id" 
                  v-model="selectedKidId"
                >
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <button @click="handleClose" class="cancel-button">Annuler</button>
        <button 
          @click="handleShare" 
          class="share-button"
          :disabled="!selectedKidId"
        >
          Lier cet enfant
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-container {
  background-color: white;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.modal-header {
  padding: 16px 20px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.25rem;
  color: #358E9D;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
}

.modal-body {
  padding: 20px;
}

.modal-description {
  margin-bottom: 20px;
  color: #555;
}

.no-kids-message {
  text-align: center;
  padding: 20px;
  color: #666;
  font-style: italic;
}

.kids-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.kid-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border: 1px solid #ddd;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.kid-item:hover {
  border-color: #358E9D;
  background-color: #f9f9f9;
}

.kid-item.selected {
  border-color: #358E9D;
  background-color: #e6f7fa;
}

.kid-info h3 {
  margin: 0 0 5px 0;
  font-size: 1.1rem;
  color: #333;
}

.kid-info p {
  margin: 0;
  font-size: 0.9rem;
  color: #666;
}

.modal-footer {
  padding: 16px 20px;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.cancel-button {
  padding: 8px 16px;
  background-color: #f5f5f5;
  border: 1px solid #ddd;
  border-radius: 4px;
  color: #555;
  cursor: pointer;
  font-weight: 500;
}

.cancel-button:hover {
  background-color: #e5e5e5;
}

.share-button {
  padding: 8px 16px;
  background-color: #FF9800;
  border: none;
  border-radius: 4px;
  color: white;
  cursor: pointer;
  font-weight: 500;
}

.share-button:hover:not(:disabled) {
  background-color: #F57C00;
}

.share-button:disabled {
  background-color: #FFD180;
  cursor: not-allowed;
}
</style>