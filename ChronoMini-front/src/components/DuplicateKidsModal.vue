<script setup lang="ts">
import { ref, computed } from 'vue';
import { mergeKids } from '@/_services/ConnectionService';
import { formatDate } from '@/_utils/dateUtils'; 

interface KidInfo {
  id: number;
  first_name: string;
  birth_date: string;
  created_at?: string;
}

interface DuplicatePair {
  kid1: KidInfo;
  kid2: KidInfo;
}

const props = defineProps({
  duplicates: {
    type: Array as () => DuplicatePair[],
    required: true
  },
  isVisible: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'merged']);

const isProcessing = ref(false);
const processingPairKey = ref('');
const errorMessage = ref('');

// Fonction pour déterminer quel enfant est le plus ancien
const determineOldestKid = (pair: any) => {
  // Si les dates de création sont disponibles
  if (pair.kid1.created_at && pair.kid2.created_at) {
    return new Date(pair.kid1.created_at) <= new Date(pair.kid2.created_at) ? pair.kid1 : pair.kid2;
  }
  // Par défaut, on considère le premier enfant (kid1) comme le plus ancien
  return pair.kid1;
};

// Préparer les paires pour la sélection
const duplicatePairs = computed(() => {
  return props.duplicates.map((pair: DuplicatePair, index) => {
    return {
      pairKey: `pair_${index}`,
      kid1: pair.kid1,
      kid2: pair.kid2
    };
  });
});

// Fusionner un pair spécifique
const mergePair = async (pairKey: string) => {
  try {
    isProcessing.value = true;
    processingPairKey.value = pairKey;
    errorMessage.value = '';
    
    const pairIndex = duplicatePairs.value.findIndex(p => p.pairKey === pairKey);
    const pair = duplicatePairs.value[pairIndex];
    
    // Déterminer quel enfant est le plus ancien
    const oldestKid = determineOldestKid(pair);
    const newestKid = oldestKid === pair.kid1 ? pair.kid2 : pair.kid1;
    
    await mergeKids({
      kid_to_keep: oldestKid.id,
      kid_to_remove: newestKid.id
    });
    
    // Supprimer la paire fusionnée
    if (pairIndex !== -1) {
      props.duplicates.splice(pairIndex, 1);
    }
    
    // Émettre un événement de succès
    emit('merged', { pairKey, kidKept: oldestKid.id });
    
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Une erreur est survenue lors de la fusion.';
  } finally {
    isProcessing.value = false;
    processingPairKey.value = '';
  }
};

// Sauter la fusion pour toutes les paires
const skipAll = () => {
  emit('close');
};
</script>

<template>
  <div v-if="isVisible && duplicatePairs.length > 0" class="duplicate-modal-overlay">
    <div class="duplicate-modal">
      <header class="modal-header">
        <h2>Enfants potentiellement en double détectés</h2>
        <button @click="skipAll" class="close-button">×</button>
      </header>
      
      <div class="modal-content">
        <p class="intro-text">
          Nous avons détecté des enfants qui pourraient être en double. Vous pouvez les fusionner pour éviter la duplication.
        </p>
        <div class="warning-box">
          <strong>Important :</strong> La fusion conservera uniquement les relevés de l'enfant créé en premier. 
          Les relevés de l'autre enfant seront supprimés. Nous vous conseillons de télécharger vos relevés 
          concernant l'enfant le plus récent avant de procéder à la fusion.
        </div>
        
        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>
        
        <div v-for="pair in duplicatePairs" :key="pair.pairKey" class="duplicate-pair">
          <h3>Enfants avec le même nom et date de naissance</h3>
          
          <div class="kids-comparison">
            <!-- Premier enfant -->
            <div class="kid-card" :class="{ 'will-keep': determineOldestKid(pair) === pair.kid1, 'will-remove': determineOldestKid(pair) !== pair.kid1 }">
              <div class="kid-header">
                <h4>{{ pair.kid1.first_name }}</h4>
                <span class="birth-date">Né(e) le {{ formatDate(pair.kid1.birth_date) }}</span>
                <span class="created-date" v-if="pair.kid1.created_at">Créé le {{ formatDate(pair.kid1.created_at) }}</span>
              </div>
              <div class="kid-status">
                {{ determineOldestKid(pair) === pair.kid1 ? 'Sera conservé (plus ancien)' : 'Sera supprimé (plus récent)' }}
              </div>
            </div>
            
            <!-- Icône de comparaison au milieu -->
            <div class="comparison-icon">
              <font-awesome-icon icon="fa-solid fa-arrows-left-right" />
            </div>
            
            <!-- Deuxième enfant -->
            <div class="kid-card" :class="{ 'will-keep': determineOldestKid(pair) === pair.kid2, 'will-remove': determineOldestKid(pair) !== pair.kid2 }">
              <div class="kid-header">
                <h4>{{ pair.kid2.first_name }}</h4>
                <span class="birth-date">Né(e) le {{ formatDate(pair.kid2.birth_date) }}</span>
                <span class="created-date" v-if="pair.kid2.created_at">Créé le {{ formatDate(pair.kid2.created_at) }}</span>
              </div>
              <div class="kid-status">
                {{ determineOldestKid(pair) === pair.kid2 ? 'Sera conservé (plus ancien)' : 'Sera supprimé (plus récent)' }}
              </div>
            </div>
          </div>
          
          <div class="action-buttons">
            <button 
              @click="mergePair(pair.pairKey)" 
              class="merge-button"
              :disabled="isProcessing"
              :class="{ 'processing': processingPairKey === pair.pairKey }"
            >
              <span v-if="processingPairKey === pair.pairKey">
                Fusion en cours...
              </span>
              <span v-else>
                Fusionner ces enfants
              </span>
            </button>
            
            <button @click="skipAll" class="skip-button">
              Ne pas fusionner
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.duplicate-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.duplicate-modal {
  background-color: white;
  border-radius: 8px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.modal-header {
  padding: 15px 20px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  color: #358E9D;
  font-size: 1.5rem;
}

.close-button {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
}

.modal-content {
  padding: 20px;
}

.intro-text {
  margin-bottom: 15px;
  color: #555;
}

.warning-box {
  background-color: #fff8e1;
  border-left: 4px solid #ffc107;
  padding: 15px;
  margin-bottom: 20px;
  border-radius: 4px;
}

.duplicate-pair {
  margin-bottom: 30px;
  padding: 20px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background-color: #f9f9f9;
}

.duplicate-pair h3 {
  margin-top: 0;
  color: #333;
  font-size: 1.2rem;
  margin-bottom: 15px;
}

.kids-comparison {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}

.kid-card {
  flex: 1;
  padding: 15px;
  border-radius: 6px;
  background-color: white;
  border: 2px solid #ddd;
  transition: all 0.3s ease;
}

.kid-card.will-keep {
  border-color: #4CAF50;
  background-color: #e8f5e9;
}

.kid-card:not(.will-keep) {
  border-color: #f44336;
  background-color: #ffebee;
}

.kid-header {
  margin-bottom: 10px;
}

.kid-header h4 {
  margin: 0;
  font-size: 1.1rem;
}

.birth-date {
  color: #666;
  font-size: 0.9rem;
}

.kid-status {
  font-weight: bold;
  color: #333;
  font-size: 0.9rem;
}

.swap-controls {
  display: flex;
  justify-content: center;
  padding: 0 15px;
}

.swap-button {
  background-color: #f5f5f5;
  border: 1px solid #ddd;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.swap-button:hover {
  background-color: #e0e0e0;
}

.action-buttons {
  display: flex;
  gap: 10px;
}

.merge-button, .skip-button {
  padding: 10px 15px;
  border-radius: 4px;
  font-weight: bold;
  cursor: pointer;
  border: none;
  transition: background-color 0.2s;
}

.merge-button {
  background-color: #568203;
  color: white;
  flex: 2;
}

.merge-button:hover:not(:disabled) {
  background-color: #568203;
}

.merge-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.merge-button.processing {
  background-color: #8bc34a;
}

.skip-button {
  background-color: #f5f5f5;
  color: #666;
  border: 1px solid #ddd;
  flex: 1;
}

.skip-button:hover {
  background-color: #e0e0e0;
}

.error-message {
  background-color: #ffebee;
  color: #b71c1c;
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 15px;
}

@media (max-width: 768px) {
  .kids-comparison {
    flex-direction: column;
    gap: 15px;
  }
  
  .swap-controls {
    transform: rotate(90deg);
    margin: 15px 0;
  }
  
  .action-buttons {
    flex-direction: column;
  }
}
</style>