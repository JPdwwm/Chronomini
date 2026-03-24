<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { getKidById, deleteKid, updateKid } from '@/_services/KidService';
import { RecordService } from '@/_services/RecordService';
import type { Kid } from '@/_models/Kid';
import type { Record } from '@/_models/Record';
import { formatDate, formatHours } from '@/_utils/dateUtils';
import LoadingIcon from '@/components/LoadingIcon.vue'; 
import router from '@/router';

const route = useRoute();
const kidId = route.params.id;
const kid = ref<Kid | null>(null);
const records = ref<Record[]>([]);
const errorMessage = ref<string | null>(null);
const isLoading = ref(true);
const showDeleteModal = ref(false);
const isDeleting = ref(false);
const isSaving = ref<{ [key in EditableFields]?: boolean }>({});
const fieldErrors = ref<{ [key in EditableFields]?: string }>({});

// Définir un type pour les champs éditables
type EditableFields = 'first_name' | 'birth_date';

const editMode = ref<{ [key in EditableFields]?: boolean }>({});
const editedValues = ref<{ [key in EditableFields]: string }>({
  first_name: '',
  birth_date: ''
});

const toggleEdit = (field: EditableFields) => {
  editMode.value[field] = !editMode.value[field];
  if (editMode.value[field] && kid.value) {
    editedValues.value[field] = kid.value[field] || '';
  }
  // Réinitialiser l'erreur quand on commence à éditer
  fieldErrors.value[field] = '';
};

const saveField = async (field: EditableFields) => {
  try {
    isSaving.value[field] = true;
    fieldErrors.value[field] = '';
    
    if (kid.value?.id) {
      const updateData: Partial<Kid> = {
        [field]: editedValues.value[field]
      };
      await updateKid(kid.value.id, updateData);
      if (kid.value) {
        kid.value[field] = editedValues.value[field];
      }
      editMode.value[field] = false;
    }
  } catch (error: any) {    
    // Récupération du message d'erreur
    if (error.response?.data?.errors && error.response.data.errors[field]) {
      // Récupérer le message d'erreur spécifique au champ
      fieldErrors.value[field] = error.response.data.errors[field][0];
    } else if (error.response?.data?.message) {
      // Utiliser le message général si disponible
      fieldErrors.value[field] = error.response.data.message;
    } else {
      // Message par défaut
      fieldErrors.value[field] = "Erreur lors de la mise à jour";
    }
  } finally {
    isSaving.value[field] = false;
  }
};

// Fonction pour gérer la suppression
const handleDelete = async () => {
  try {
    isDeleting.value = true;
    await deleteKid(Number(kidId));
    router.push('/kids'); // Redirection vers la liste des enfants
  } catch (error: any) {
    errorMessage.value = "Erreur lors de la suppression de l'enfant";
  } finally {
    isDeleting.value = false;
    showDeleteModal.value = false;
  }
};

const loadKidAndRecords = async () => {
  try {
    isLoading.value = true;
    // Charger l'enfant et ses enregistrements en parallèle
    const [kidData, recordsData] = await Promise.all([
      getKidById(Number(kidId)),
      RecordService.getKidRecords(Number(kidId))
    ]);
    
    kid.value = kidData;
    records.value = recordsData;
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Une erreur est survenue.';
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  loadKidAndRecords();
});

// Calcul des statistiques à partir des enregistrements
const recordsCount = computed(() => records.value.length);
const lastRecordDate = computed(() => {
  const sortedRecords = [...records.value].sort((a, b) => 
    new Date(b.date || '').getTime() - new Date(a.date || '').getTime()
  );
  return sortedRecords[0]?.date;
});
</script>

<template>
  <LoadingIcon v-if="isLoading" />
  <main class="container">
    <div v-if="errorMessage" class="error-card">
      <p>{{ errorMessage }}</p>
    </div>

    <div v-else-if="kid" class="kid-profile">
      <header class="kid-header">
  <div class="field-value">
    <template v-if="!editMode.first_name">
  <h1>{{ kid.first_name }}</h1>
  <font-awesome-icon 
    icon="fa-solid fa-pen"  
    class="edit-icon" 
    @click="toggleEdit('first_name')" 
  />
</template>
<div v-else class="edit-inputs">
  <div class="input-wrapper">
    <input v-model="editedValues.first_name" placeholder="Prénom" />
    <div v-if="fieldErrors.first_name" class="field-error">
      {{ fieldErrors.first_name }}
    </div>
  </div>
  <div class="edit-actions">
    <font-awesome-icon 
      v-if="isSaving.first_name"
      icon="fa-solid fa-spinner"
      class="spinner-icon" 
      spin
    />
    <template v-else>
      <font-awesome-icon 
        icon="fa-solid fa-check"
        class="save-icon" 
        @click="saveField('first_name')" 
      />
      <font-awesome-icon 
        icon="fa-solid fa-ban"
        class="cancel-icon" 
        @click="toggleEdit('first_name')" 
      />
    </template>
  </div>
</div>
  </div>

  <div class="field-value">
    <template v-if="!editMode.birth_date">
      <p class="birth-date">
        Né(e) le {{ kid.birth_date ? formatDate(kid.birth_date) : 'Non renseigné' }}
        <font-awesome-icon 
          icon="fa-solid fa-pen"  
          class="edit-icon" 
          @click="toggleEdit('birth_date')" 
        />
      </p>
    </template>
    <div v-else class="edit-inputs">
      <input 
        v-model="editedValues.birth_date" 
        type="date"
      />
      <div class="edit-actions">
        <font-awesome-icon 
          icon="fa-solid fa-check"
          class="save-icon" 
          @click="saveField('birth_date')" 
        />
        <font-awesome-icon 
          icon="fa-solid fa-ban"
          class="cancel-icon" 
          @click="toggleEdit('birth_date')" 
        />
      </div>
    </div>
  </div>
  <button @click="showDeleteModal = true" class="delete-button">
        <font-awesome-icon icon="fa-solid fa-trash" />
        Supprimer
      </button>
</header>
      <div class="action-cards">
        <router-link :to="`/record/${kid.id}`" class="action-card new-record">
          <font-awesome-icon icon="fa-solid fa-play" class="card-icon" />
          <div class="card-content">
            <h3>Enregistrement</h3>
            <p>Démarrer ou arrêter une session d'enregistrement</p>
          </div>
        </router-link>
        <router-link :to="`/kid/${kid.id}/records`" class="action-card history">
          <font-awesome-icon icon="fa-solid fa-clock-rotate-left" class="card-icon" />
          <div class="card-content">
            <h3>Historique</h3>
            <p>Voir tous les enregistrements précédents</p>
          </div>
        </router-link>
      </div>
      <section class="kid-stats" v-if="records.length > 0">
        <h2>Résumé des activités</h2>
        <div class="stats-grid">
          <div class="stat-card">
            <h4>Total enregistrements</h4>
            <p>{{ recordsCount }}</p>
          </div>
          <div class="stat-card">
            <h4>Dernier enregistrement</h4>
            <p>{{ lastRecordDate ? formatDate(lastRecordDate) : 'Aucun' }}</p>
          </div>
        </div>
      </section>
      <section class="recent-records" v-if="records.length > 0">
        <h2>Derniers enregistrements</h2>
        <div class="records-list">
          <div v-for="record in records.slice(0, 5)" :key="record.id" class="record-item">
            <div class="record-date">{{ record.date ? formatDate(record.date) : '' }}</div>
            <div class="record-hours">
              <span>Arrivée : {{ record.drop_hour }}</span>
              <span>Départ : {{ record.pick_up_hour || 'En cours' }}</span>
            </div>
            <div class="record-total" v-if="record.amount_hours">
              Total : {{ formatHours(record.amount_hours) }}
            </div>
          </div>
        </div>
        <router-link :to="`/kid/${kid.id}/records`" class="view-all-link">
          Voir tous les enregistrements
        </router-link>
      </section>
    </div>
    <div v-if="showDeleteModal" class="modal-overlay">
      <div class="modal-content">
        <h2>Confirmer la suppression</h2>
        <p>Êtes-vous sûr de vouloir supprimer {{ kid?.first_name }} ?</p>
        <p class="warning">Cette action est irréversible et vous n'aurez plus accès aux relevés concernant cet enfant.</p>
        
        <div class="modal-actions">
          <button 
            @click="handleDelete" 
            class="delete-confirm-button"
            :disabled="isDeleting"
          >
            {{ isDeleting ? 'Suppression...' : 'Supprimer' }}
          </button>
          <button 
            @click="showDeleteModal = false" 
            class="cancel-button"
            :disabled="isDeleting"
          >
            Annuler
          </button>
        </div>
      </div>
      </div>
  </main>
</template>

<style scoped>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  font-family: Georgia, 'Times New Roman', Times, serif;
}

.field-value {
  display: flex;
  align-items: center;
  gap: 10px;
  justify-content: center;
}

.edit-icon {
  cursor: pointer;
  color: #666;
  opacity: 0.6;
  transition: opacity 0.2s;
}

.edit-icon:hover {
  opacity: 1;
}

.edit-inputs {
  display: flex;
  gap: 10px;
  align-items: center;
}

.edit-inputs input {
  padding: 8px;
  border: 1px solid #c3c6ce;
  border-radius: 8px;
  font-size: 1rem;
}

.save-icon {
  cursor: pointer;
  color: #78BB99;
}

.cancel-icon {
  cursor: pointer;
  color: #E26D5C;
}

.input-wrapper {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.field-error {
  color: #E26D5C;
  font-size: 0.8rem;
  margin-top: 4px;
  text-align: left;
}

.spinner-icon {
  color: #358E9D;
}

.kid-profile {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.kid-header {
  text-align: center;
  margin-bottom: 2rem;
}

.kid-header h1 {
  font-size: 2.5rem;
  color: #333;
  margin-bottom: 0.5rem;
}

.birth-date {
  color: #666;
  font-style: italic;
}

.action-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
  margin: 2rem 0;
}

.action-card {
  display: flex;
  align-items: center;
  padding: 1.8rem;
  background: #f5f5f5; /* Même couleur que les autres cartes */
  border-radius: 20px; /* Cohérent avec les autres cartes */
  border: 2px solid #c3c6ce; /* Même style de bordure */
  transition: all 0.3s ease;
  text-decoration: none;
  color: inherit;
}

.action-card:hover {
  transform: translateY(-5px);
  border-color: #78BB99;;
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.25);
}

.card-icon {
  font-size: 2.2rem;
  margin-right: 1.5rem;
  color: #358E9D; /* Cohérent avec la couleur principale */
  transition: transform 0.3s ease;
}

.action-card:hover .card-icon {
  transform: scale(1.1);
}

.card-content h3 {
  margin: 0;
  color: #333;
  font-size: 1.3rem;
  font-weight: bold;
}

.card-content p {
  margin: 0.5rem 0 0;
  color: #666;
  font-size: 1rem;
}

.kid-stats, .recent-records {
  background: #f5f5f5;
  border-radius: 20px;
  padding: 2rem;
  border: 2px solid #c3c6ce;
  transition: all 0.3s ease;
}

.kid-stats:hover, .recent-records:hover {
  border-color: #78BB99;
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.25);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.stat-card {
  text-align: center;
  padding: 1.5rem;
  background: #f5f5f5;
  border-radius: 15px;
  transition: all 0.3s ease;
  border: 2px solid #c3c6ce
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-card h4 {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}

.stat-card p {
  margin: 0.5rem 0 0;
  color: #333;
  font-size: 1.5rem;
  font-weight: bold;
}

.recent-records {
  margin-top: 2rem;
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.records-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1rem;
}

.record-item {
  padding: 1.2rem;
  background: white;
  border-radius: 15px;
  display: grid;
  grid-template-columns: 1fr 2fr 1fr;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
}

.record-item:hover {
  transform: translateX(5px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.record-hours {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.view-all-link {
  display: block;
  text-align: center;
  margin-top: 1.5rem;
  color: #78BB99;
  text-decoration: none;
  font-weight: 500;
}

.view-all-link:hover {
  text-decoration: underline;
}

.error-card {
  background: #fee;
  color: #c00;
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
}

.delete-button {
  align-self: flex-end; /* Pour aligner le bouton à droite */
  padding: 0.5rem 1rem;
  background-color: #E26D5C;;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: background-color 0.2s;
}

.delete-button:hover {
  background-color: #c85a4a;
}

.modal-overlay {
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

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  width: 90%;
  max-width: 400px;
  text-align: center;
}

.warning {
  color: #dc3545;
  font-size: 0.9rem;
  margin: 1rem 0;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 1.5rem;
}

.delete-confirm-button {
  padding: 0.5rem 1rem;
  background-color: #E26D5C;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.delete-confirm-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.cancel-button {
  padding: 0.5rem 1rem;
  background-color: #358E9D;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.cancel-button:hover {
  background-color: #2d7a87;
}

.cancel-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .action-cards {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .record-item {
    grid-template-columns: 1fr;
    text-align: center;
  }
}
</style>