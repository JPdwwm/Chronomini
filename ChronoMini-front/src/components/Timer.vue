<script setup lang="ts">
import { ref, computed, onUnmounted, watch, onMounted } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { useBreakStore } from '@/stores/break';

// Initialiser le store
const breakStore = useBreakStore();

const isPaused = ref(false);
const pausedTime = ref<Date | null>(null);
const props = defineProps<{
  initialIsTracking?: boolean
  initialStartTime?: {
    date: string
    hour: string
  } | null | undefined
  recordId?: number | null
}>();

const isTracking = ref(false);
const startTime = ref<Date | null>(null);
const elapsedTime = ref<string | null>(null);
const timer = ref<number | null>(null);
const internalRecordId = ref<number | null>(null);
const isClicked = ref(false);

const emit = defineEmits<{
  'start-record': []
  'stop-record': []
}>();

// Vérifier l'état de pause au chargement
onMounted(async () => {
  if (props.recordId) {
    internalRecordId.value = props.recordId;
    isPaused.value = await breakStore.syncWithServer(props.recordId);
    
    // Si on est en pause, arrêter le timer
    if (isPaused.value) {
      pausedTime.value = new Date();
      stopTimer();
    }
  }
});

const handlePauseClick = async () => {
  // Utiliser soit internalRecordId soit props.recordId
  const recordId = internalRecordId.value || props.recordId;
  
  if (!recordId) {
    return;
  }
  
  if (!isPaused.value) {
    const success = await breakStore.startBreak(recordId);
    
    if (success) {
      isPaused.value = true;
      pausedTime.value = new Date();
      stopTimer();
    }
  } else {
    const success = await breakStore.endBreak(recordId);
    
    if (success) {
      isPaused.value = false;
      startTimer();
    }
  }
};

const startTimer = () => {
  timer.value = window.setInterval(() => {
    if (startTime.value && !isPaused.value) {
      const now = new Date();
      
      // Si on avait un temps de pause, l'ajouter à startTime pour compenser
      let diff;
      if (pausedTime.value) {
        const pauseDuration = now.getTime() - pausedTime.value.getTime();
        // Ajuster le temps de départ pour tenir compte de la durée de pause
        startTime.value = new Date(startTime.value.getTime() + pauseDuration);
        pausedTime.value = null; // Réinitialiser le temps de pause
      }
      
      diff = now.getTime() - startTime.value.getTime();
      const hours = Math.floor(diff / (1000 * 60 * 60));
      const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((diff % (1000 * 60)) / 1000);

      elapsedTime.value = `${hours}h ${minutes}min ${seconds}s`;
    }
  }, 1000);
};

const initializeTimer = () => {
  if (props.initialIsTracking && props.initialStartTime) {
    isTracking.value = true;
    const startDateTime = `${props.initialStartTime.date} ${props.initialStartTime.hour}`;
    startTime.value = new Date(startDateTime);
    startTimer();
  } else {
    isTracking.value = false;
    startTime.value = null;
    elapsedTime.value = '0h 0min 0s';
  }
};

const stopTimer = () => {
  if (timer.value) {
    clearInterval(timer.value);
    timer.value = null;
  }
};

const handleClick = async () => {
  isClicked.value = true;

  if (!isTracking.value) {
    emit('start-record');
    isTracking.value = true;
    startTime.value = new Date();
    startTimer();
    
    // Réinitialiser internalRecordId quand on démarre un nouvel enregistrement
    internalRecordId.value = null;
  } else {
    emit('stop-record');
    isTracking.value = false;
    isPaused.value = false;
    stopTimer();
    elapsedTime.value = '0h 0min 0s';
    startTime.value = null;
    internalRecordId.value = null;
  }

  setTimeout(() => {
    isClicked.value = false;
  }, 1000);
};

// Formatage du temps écoulé
const formattedTime = computed(() => {
  return elapsedTime.value || '0h 0min 0s';
});

const updateRecordIdManually = (newId: number) => {
  if (newId) {
    internalRecordId.value = newId;
  }
};

defineExpose({
  updateRecordIdManually
});

// Observer les changements des props et du recordId
watch([() => props.initialIsTracking, () => props.initialStartTime], () => {
  initializeTimer();
}, { immediate: true });

// Observer les changements de recordId pour mettre à jour internalRecordId
watch(() => props.recordId, (newRecordId) => {
  if (newRecordId) {
    internalRecordId.value = newRecordId;
  }
}, { immediate: true });

// Ajouter une surveillance du recordId pour vérifier l'état de pause
watch(() => props.recordId, async (newRecordId) => {
  if (newRecordId) {
    isPaused.value = await breakStore.syncWithServer(newRecordId);
    
    // Si on est en pause, arrêter le timer
    if (isPaused.value) {
      pausedTime.value = new Date();
      stopTimer();
    } else if (isTracking.value) {
      // Si on est en tracking mais pas en pause, s'assurer que le timer est démarré
      startTimer();
    }
  } else {
    isPaused.value = false;
  }
}, { immediate: true });

onUnmounted(() => {
  stopTimer();
});
</script>

<template>
  <div class="main-wrapper">
    <div class="container-btn">
      <button 
        class="btn" 
        :class="{ active: isClicked }" 
        @click="handleClick"
      >
        <span class="icon">
          <font-awesome-icon :icon="!isTracking ? 'fa-solid fa-play' : 'fa-solid fa-stop'" />
        </span>
        <span class="text">
          {{ !isTracking ? 'Valider la dépose' : 'Valider la récupération' }}
        </span>
      </button>
    </div>
    <div class="controls-container">
      <div class="pause-btn-container">
        <button class="pause-btn" @click="handlePauseClick" :class="{ 'pause-active': isPaused }">
          <font-awesome-icon :icon="!isPaused ? 'fa-solid fa-pause' : 'fa-solid fa-play'" class="pause-icon" />
          <span>{{ !isPaused ? 'Pause' : 'Reprendre' }}</span>
        </button>
      </div>
      <div class="timer-container">
        <svg 
          class="progress-circle" 
          :class="{ active: isTracking && !isPaused }" 
          viewBox="0 0 150 150"
        >
          <circle class="bg" cx="75" cy="75" r="70" />
          <circle class="progress" cx="75" cy="75" r="70" />
        </svg>
        <div class="timer">
          {{ formattedTime }}
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.timer-container {
  position: relative;
  width: 160px;
  height: 160px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.progress-circle {
  position: absolute;
  width: 120px;
  height: 120px;
  transform: rotate(-90deg);
}

.btn{
  cursor: pointer;
}

.progress {
  stroke: #F7CD79;
  stroke-dasharray: 440;
  stroke-dashoffset: 440;
  stroke-linecap: round;
  transition: stroke-dashoffset 0.3s ease;
}

.progress-circle.active .progress {
  animation: progressAnimation 60s linear infinite;
}

circle {
  fill: none;
  stroke-width: 10;
  r: 70;
  cx: 75;
  cy: 75;
}

.timer {
  position: absolute;
  top: 50px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 1rem;
  font-weight: bold;
  color: #333;
  z-index: 1;
}

.bg {
  stroke: #e0e0e0;
}

.controls-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  margin-top: 2rem;
}

.pause-btn-container {
  flex: 1;
  display: flex;
  justify-content: center;
  padding-right: 2rem;
}

.pause-btn {
  width: 120px;
  height: 120px;
  background: #358E9D;
  border-radius: 50%;
  color: white;
  border: none;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
}

.pause-btn.pause-active {
  background: #e74c3c;
}

.pause-btn .pause-icon {
  font-size: 2rem;
}

.pause-btn:hover {
  background: #2a7180;
}

.container-btn {
  width: 100%;
  justify-content: center;
}

@keyframes progressAnimation {
  from {
    stroke-dashoffset: 440;
  }
  to {
    stroke-dashoffset: 0;
  }
}

/* Styles du bouton */
.btn {
  position: relative;
  width: 280px;
  height: 60px;
  margin: 0 auto;
  background: #F7CD79;
  border-radius: 60px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: grey;
  letter-spacing: 2px;
  border: none;
  padding: 0 20px;
  transition: color 0.5s;
  overflow: hidden;
}

.btn .icon {
  position: absolute;
  left: 5px;
  width: 50px;
  height: 50px;
  background: #358E9D;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 1.5rem;
  transition: left 0.5s ease-in-out;
  color: white;
}

.btn .text {
  flex-grow: 1;
  text-align: center;
  font-size: 1rem;
  white-space: nowrap;
  margin-left: 45px;
}

.btn.active .icon {
  left: calc(100% - 55px);
}

.btn.active .text {
  opacity: 0.8;
}

.btn:after {
  content: '';
  position: absolute;
  width: 50px;
  height: 100%;
  z-index: 1;
  background: rgb(239, 232, 232);
  transform: translateX(-180px) skewX(30deg);
  transition: transform 0.7s ease-in-out;
}

.btn.active {
  padding-left: 0px;
  padding-right: 40px;
  color: rgb(255, 255, 255);
}

.btn.active span {
  left: calc(100% - 55px);
  transition: left 0.7s ease-in-out;
}

.btn.active:after {
  transform: translateX(300px) skewX(30deg);
}

.container-btn {
  margin-top: 20px;
}
</style>