<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { RecordService } from '@/_services/RecordService';
import { getKidById } from '@/_services/KidService';
import { getUser } from '@/_services/UserService';
import type { Record } from '@/_models/Record';
import type { Kid } from '@/_models/Kid';
import type { User } from '@/_models/User';
import { formatDate, formatHours, escapeHtml } from '@/_utils/dateUtils';

import html2pdf from 'html2pdf.js';

const route = useRoute();
const router = useRouter();
const recordId = route.params.id;

const record = ref<Record | null>(null);
const kid = ref<Kid | null>(null);
const user = ref<User | null>(null);
const errorMessage = ref<string | null>(null);
const isLoading = ref(true);
const newAnnotation = ref('');
const showAddAnnotation = ref(false);
const isSubmitting = ref(false);
const submitError = ref('');
const isPdfGenerating = ref(false);

const loadRecordDetails = async () => {
  try {
    isLoading.value = true;
    // Charger le relevé
    const recordData = await RecordService.getRecordById(Number(recordId));
    record.value = recordData;

    // Charger les informations de l'enfant
    if (recordData.kid_id) {
      const kidData = await getKidById(recordData.kid_id);
      kid.value = kidData;
    }

    // Charger les informations de l'utilisateur
    if (recordData.user_id) {
      const userData = await getUser();
      user.value = userData;
    }
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Une erreur est survenue.';
  } finally {
    isLoading.value = false;
  }
};

const addAnnotation = async () => {
  if (!newAnnotation.value.trim() || !record.value) return;
  
  try {
    isSubmitting.value = true;
    submitError.value = '';
    
    // Utiliser la nouvelle méthode spécifique aux annotations
    const response = await RecordService.addAnnotation(Number(recordId), newAnnotation.value);
    
    // Mettre à jour le record local avec la nouvelle annotation
    if (record.value) {
      record.value.annotation = newAnnotation.value;
    }
    
    // Réinitialiser le formulaire
    newAnnotation.value = '';
    showAddAnnotation.value = false;
  } catch (error: any) {
    submitError.value = error.response?.data?.message || "Erreur lors de l'enregistrement de l'annotation";
  } finally {
    isSubmitting.value = false;
  }
};

// Fonction pour télécharger le contenu en PDF
const downloadAsPdf = () => {
  if (!record.value || !kid.value) return;
  
  isPdfGenerating.value = true;
  
  // Créer un élément temporaire pour le PDF
  const pdfContent = document.createElement('div');
  pdfContent.className = 'pdf-content';
  pdfContent.innerHTML = `
    <div class="pdf-container">
      <header class="pdf-header">
        <h1>Détail du relevé</h1>
        <p class="pdf-date">${formatDate(record.value.date || '')}</p>
      </header>
      
      <section class="pdf-info-card">
        <div class="pdf-grid">
          <div class="pdf-grid-item">
            <h3>Enfant</h3>
            <p>${escapeHtml(kid.value.first_name)}</p>
          </div>
          <div class="pdf-grid-item">
            <h3>Créateur du relevé</h3>
            <p>${escapeHtml(user.value?.first_name || '')}</p>
          </div>
          <div class="pdf-grid-item">
            <h3>Heure d'arrivée</h3>
            <p>${record.value.drop_hour}</p>
          </div>
          <div class="pdf-grid-item">
            <h3>Heure de départ</h3>
            <p>${record.value.pick_up_hour || 'En cours'}</p>
          </div>
          <div class="pdf-grid-item">
            <h3>Durée totale</h3>
            <p>${record.value.amount_hours ? formatHours(record.value.amount_hours) : 'En cours'}</p>
          </div>
        </div>
      </section>
      
      <section class="pdf-annotations">
        <h2>Annotations</h2>
        ${record.value.annotation
          ? `<div class="pdf-annotation-content"><p>${escapeHtml(record.value.annotation)}</p></div>`
          : `<p class="pdf-no-annotations">Aucune annotation pour le moment</p>`}
      </section>
    </div>
  `;
  
  // Ajouter le style au contenu PDF
  const style = document.createElement('style');
  style.textContent = `
    .pdf-container {
      font-family: Arial, sans-serif;
      color: #333;
      padding: 20px;
    }
    
    .pdf-header {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .pdf-header h1 {
      font-size: 24px;
      margin-bottom: 5px;
    }
    
    .pdf-date {
      color: #666;
      font-style: italic;
    }
    
    .pdf-info-card {
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 10px;
      margin-bottom: 30px;
    }
    
    .pdf-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }
    
    .pdf-grid-item {
      text-align: center;
    }
    
    .pdf-grid-item h3 {
      color: #666;
      font-size: 14px;
      margin-bottom: 5px;
    }
    
    .pdf-grid-item p {
      font-size: 16px;
      font-weight: 500;
    }
    
    .pdf-annotations {
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 10px;
    }
    
    .pdf-annotations h2 {
      font-size: 18px;
      margin-bottom: 15px;
    }
    
    .pdf-annotation-content {
      background: #f9f9f9;
      padding: 15px;
      border-radius: 5px;
    }
    
    .pdf-no-annotations {
      text-align: center;
      color: #666;
      font-style: italic;
    }
  `;
  
  pdfContent.appendChild(style);
  
  // Configurer les options PDF
  const options = {
    margin: 10,
    filename: `releve_${kid.value.first_name}_${formatDate(record.value.date || '')}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true, logging: true },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
  };
  
  // Ajouter temporairement l'élément au DOM (nécessaire pour html2pdf)
  document.body.appendChild(pdfContent);
  
  // Générer et télécharger le PDF
  html2pdf()
    .from(pdfContent)
    .set(options)
    .save()
    .then(() => {
      // Nettoyer en supprimant l'élément temporaire
      document.body.removeChild(pdfContent);
      isPdfGenerating.value = false;
    })
    .catch((error: any) => {
      // Nettoyer même en cas d'erreur
      if (document.body.contains(pdfContent)) {
        document.body.removeChild(pdfContent);
      }
      isPdfGenerating.value = false;
    });
};

onMounted(() => {
  loadRecordDetails();
});
</script>

<template>
  <main class="container">
    <div v-if="isLoading" class="loading">
      Chargement...
    </div>

    <div v-else-if="errorMessage" class="error-card">
      <p>{{ errorMessage }}</p>
    </div>

    <div v-else-if="record && kid && user" class="record-detail">
      <header class="record-header">
        <h1>Détail du relevé</h1>
        <p class="record-date">{{ formatDate(record.date || '') }}</p>
      </header>
      
      <section class="main-card">
        <div class="info-grid">
          <div class="info-item">
            <h3>Enfant</h3>
            <p>{{ kid.first_name }}</p>
          </div>
          <div class="info-item">
            <h3>Créateur du relevé</h3>
            <p>{{ user.first_name }}</p>
          </div>
          <div class="info-item">
            <h3>Heure d'arrivée</h3>
            <p>{{ record.drop_hour }}</p>
          </div>
          <div class="info-item">
            <h3>Heure de départ</h3>
            <p>{{ record.pick_up_hour || 'En cours' }}</p>
          </div>
          <div class="info-item">
            <h3>Durée totale</h3>
            <p>{{ record.amount_hours ? formatHours(record.amount_hours) : 'En cours' }}</p>
          </div>
        </div>
      </section>
      
      <section class="annotations-section">
        <div class="annotations-header">
          <h2>Annotations</h2>
          <button 
            v-if="!showAddAnnotation"
            @click="showAddAnnotation = true"
            class="add-annotation-button"
          >
            <font-awesome-icon icon="fa-solid fa-plus" />
            Ajouter une annotation
          </button>
        </div>

        <div v-if="showAddAnnotation" class="add-annotation-form">
          <textarea 
            v-model="newAnnotation"
            placeholder="Votre annotation..."
            class="annotation-input"
          ></textarea>
          <div v-if="submitError" class="error-message">{{ submitError }}</div>
          <div class="annotation-actions">
            <button 
              @click="addAnnotation"
              class="save-button"
              :disabled="!newAnnotation.trim() || isSubmitting"
            >
              <span v-if="isSubmitting">Enregistrement...</span>
              <span v-else>Enregistrer</span>
            </button>
            <button 
              @click="showAddAnnotation = false"
              class="cancel-button"
              :disabled="isSubmitting"
            >
              Annuler
            </button>
          </div>
        </div>

        <div class="annotations-list">
          <div 
            v-if="record.annotation"
            class="annotation-item"
          >
            <p class="annotation-text">{{ record.annotation }}</p>
          </div>
          <p v-else class="no-annotations">
            Aucune annotation pour le moment
          </p>
        </div>
      </section>
      
      <div class="actions">
        <button 
          @click="router.push(`/kid/${kid.id}/records`)"
          class="back-button"
        >
          <font-awesome-icon icon="fa-solid fa-arrow-left" />
          Retour aux relevés
        </button>
        
        <button 
          @click="downloadAsPdf"
          class="pdf-button"
          :disabled="isPdfGenerating"
        >
          <font-awesome-icon icon="fa-solid fa-file-pdf" />
          <span v-if="isPdfGenerating">Génération du PDF...</span>
          <span v-else>Télécharger en PDF</span>
        </button>
      </div>
    </div>
  </main>
</template>

<style scoped>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.record-detail {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.record-header {
  text-align: center;
  margin-bottom: 2rem;
}

.record-header h1 {
  font-size: 2.5rem;
  color: #333;
  margin-bottom: 0.5rem;
}

.record-date {
  color: #666;
  font-style: italic;
}

.main-card {
  background: #f5f5f5;
  border-radius: 20px;
  padding: 2rem;
  border: 2px solid #c3c6ce;
  transition: all 0.3s ease;
}

.main-card:hover {
  border-color: #78BB99;
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.25);
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
}

.info-item {
  text-align: center;
}

.info-item h3 {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.info-item p {
  color: #333;
  font-size: 1.2rem;
  font-weight: 500;
}

.annotations-section {
  background: #f5f5f5;
  border-radius: 20px;
  padding: 2rem;
  border: 2px solid #c3c6ce;
}

.annotations-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.add-annotation-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background-color: #78BB99;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.add-annotation-button:hover {
  background-color: #69a688;
}

.add-annotation-form {
  margin-bottom: 1.5rem;
}

.annotation-input {
  width: 100%;
  min-height: 100px;
  padding: 1rem;
  border: 1px solid #c3c6ce;
  border-radius: 8px;
  margin-bottom: 1rem;
  resize: vertical;
}

.annotation-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.save-button, .cancel-button, .pdf-button {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.save-button {
  background-color: #78BB99;
  color: white;
}

.save-button:hover:not(:disabled) {
  background-color: #69a688;
}

.save-button:disabled, .cancel-button:disabled, .pdf-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.cancel-button {
  background-color: #E26D5C;
  color: white;
}

.cancel-button:hover:not(:disabled) {
  background-color: #c85a4a;
}

.annotations-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.annotation-item {
  background: white;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #c3c6ce;
}

.annotation-text {
  margin: 0;
  color: #333;
  white-space: pre-wrap;
}

.no-annotations {
  text-align: center;
  color: #666;
  font-style: italic;
}

.actions {
  display: flex;
  justify-content: space-between;
  margin-top: 2rem;
}

.back-button, .pdf-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.8rem 1.5rem;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.back-button {
  background-color: #358E9D;
}

.back-button:hover {
  background-color: #2d7a87;
}

.pdf-button {
  background-color: #E26D5C;
}

.pdf-button:hover:not(:disabled) {
  background-color: #c85a4a;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.error-card, .error-message {
  background: #fee;
  color: #c00;
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
  margin-bottom: 1rem;
}

@media (max-width: 768px) {
  .info-grid {
    grid-template-columns: 1fr;
  }

  .annotations-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }

  .add-annotation-button {
    justify-content: center;
  }

  .actions {
    flex-direction: column;
    gap: 1rem;
  }

  .back-button, .pdf-button {
    width: 100%;
    justify-content: center;
  }
}
</style>