<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import Timer from '@/components/Timer.vue';
import type { RecordedKid } from '@/_models/RecordedKid';
import type { Record } from '@/_models/Record';
import { getKidById } from '@/_services/KidService';
import RecordService from '@/_services/RecordService';
import { useRecordStore } from '../stores/record';

const isLoading = ref(false);
const error = ref<string | null>(null);
const route = useRoute();
const kidId = route.params.id;
const activeRecord = ref<Record | null>(null);
const recordStore = useRecordStore();
const timerRef = ref<InstanceType<typeof Timer> | null>(null);
const kid = ref<RecordedKid>({
  id: 1,
  first_name: '',
});

// Ajout des refs pour la date et l'heure
const currentDate = ref(new Date().toLocaleDateString('fr-FR', {
  weekday: 'long',
  year: 'numeric',
  month: 'long',
  day: 'numeric'
}));
const currentTime = ref(new Date().toLocaleTimeString('fr-FR', {
  hour: '2-digit',
  minute: '2-digit'
}));

// Mise à jour de l'heure toutes les secondes
const updateTime = () => {
  currentTime.value = new Date().toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Chargement des données de l'enfant
const loadKid = async () => {
  isLoading.value = true;
  try {
    const kidData = await getKidById(Number(kidId));
    kid.value = kidData;
  } catch (error: any) {
    error.value = error.response?.data?.message || 'Une erreur est survenue.';
  } finally {
    isLoading.value = false;
  }
}

// Vérification d'un enregistrement actif au chargement
const checkActiveRecord = async () => {
  if (!kid.value.id) return;

  try {
    const response = await RecordService.getMyRecords();
    const record = response.data.find(
      (record: Record) => record.kid_id === kid.value.id && !record.pick_up_hour
    );
    
    if (record) {
      activeRecord.value = record;
    }
  } catch (e) {
    error.value = "Erreur lors de la vérification des enregistrements";
  }
};

const handleStartRecord = async () => {
  if (!kid.value.id) {
    error.value = "ID de l'enfant non disponible";
    return;
  }

  try {
    const response = await RecordService.startRecording(kid.value.id);
    
    if (response && response.record) {
      activeRecord.value = response.record;
      
      // Mise à jour directe du Timer avec le nouvel ID
      if (timerRef.value && activeRecord.value?.id) {
        timerRef.value.updateRecordIdManually(activeRecord.value.id);
      }
    }
    
    await recordStore.checkActiveRecord();
  } catch (e: any) {
    error.value = e.response?.data?.message || "Erreur lors du démarrage de l'enregistrement";
  }
};

const handleStopRecord = async () => {
  if (!kid.value.id) {
    error.value = "ID de l'enfant non disponible";
    return;
  }

  try {
    await RecordService.stopRecording(kid.value.id);
    activeRecord.value = null;
    await recordStore.checkActiveRecord(); 
  } catch (e: any) {
    error.value = e.response?.data?.message || "Erreur lors de l'arrêt de l'enregistrement";
  }
};

onMounted(() => {
  loadKid();
  checkActiveRecord();
  // Démarrer la mise à jour de l'heure
  const timeInterval = setInterval(updateTime, 1000);

  // Nettoyage à la destruction du composant
  onUnmounted(() => {
    clearInterval(timeInterval);
  });
});
</script>

<template>
  <div class="main-container">
    <div class="time-tracking-card">
      <div v-if="isLoading">Chargement...</div>
      <p v-else-if="error" class="text-red-500">
        {{ error }}
      </p>
      <div v-else class="content-wrapper">
        <div class="date-section">
          <p class="date">
            {{ currentDate }}
          </p>
        </div>
        <div class="name-section">
          <h3 class="kid-name">
            {{ kid.first_name }}
          </h3>
        </div>
        <div class="timer-section">
          <p class="current-time">{{ currentTime }}</p>
          <Timer 
            ref="timerRef"
            :initial-is-tracking="!!activeRecord"
            :initial-start-time="activeRecord && activeRecord.date && activeRecord.drop_hour ? {
              date: activeRecord.date,
              hour: activeRecord.drop_hour
            } : undefined"
            :record-id="activeRecord ? activeRecord.id : null"
            @start-record="handleStartRecord"
            @stop-record="handleStopRecord"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.main-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: calc(100vh - 120px);
  background-color: #f9f9f9;
  font-family: Georgia, 'Times New Roman', Times, serif;
}

.time-tracking-card {
  width: 100%;
  max-width: 31.25rem;
  height: 100%;
  padding: 2rem;
}

.content-wrapper {
  display: flex;
  flex-direction: column;
  height: 100%;
  align-items: center;
  text-align: center;
  justify-content: space-between;
}

.date-section {
  margin-bottom: 2rem;
}

.date {
  font-size: 1.875rem;
}

.name-section {
  margin: 1rem 0;
}

.kid-name {
  font-size: 1.875rem;
  font-weight: 600;
  color: #358E9D;
}

.time-section {
  width: 100%;
  margin-bottom: 2rem;
}

.current-time {
  font-size: 1.875rem;
  margin-bottom: 1rem;
}
</style>