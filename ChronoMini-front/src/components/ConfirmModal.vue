<script setup lang="ts">
import { watch } from 'vue';

const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Confirmation'
  },
  message: {
    type: String,
    default: 'Êtes-vous sûr de vouloir effectuer cette action ?'
  }
});

const emit = defineEmits(['confirm', 'cancel']);

const confirm = () => {
  emit('confirm');
};

const cancel = () => {
  emit('cancel');
};

// Empêcher le défilement quand la modal est ouverte
watch(() => props.isVisible, (isVisible) => {
  if (isVisible) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
});
</script>

<template>
  <div v-if="isVisible" class="modal-overlay" @click="cancel">
    <div class="modal-container" @click.stop>
      <div class="modal-header">
        <h3>{{ title }}</h3>
        <button class="close-button" @click="cancel">&times;</button>
      </div>
      <div class="modal-content">
        <p>{{ message }}</p>
      </div>
      <div class="modal-footer">
        <button class="cancel-button" @click="cancel">Annuler</button>
        <button class="confirm-button" @click="confirm">Confirmer</button>
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
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-container {
  background-color: white;
  width: 90%;
  max-width: 500px;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #eee;
}

.modal-header h3 {
  margin: 0;
  color: #358E9D;
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

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 15px 20px;
  border-top: 1px solid #eee;
}

.cancel-button, .confirm-button {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  font-weight: bold;
  cursor: pointer;
}

.cancel-button {
  background-color: #f5f5f5;
  color: #333;
}

.confirm-button {
  background-color: #E26D5C;
  color: white;
}

.cancel-button:hover {
  background-color: #e0e0e0;
}

.confirm-button:hover {
  background-color: #E26D5C;
}
</style>