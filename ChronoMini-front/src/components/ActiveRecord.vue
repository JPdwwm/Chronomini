<script setup lang="ts">
defineProps<{
  kidName: string
}>();
</script>

<template>
  <div class="notification">
    <div class="notification-content">
      <span class="pulse"></span>
      <p>Enregistrement en cours pour {{ kidName }}</p>
    </div>
  </div>
</template>

<style scoped>
.notification {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background-color: #358E9D;
  color: white;
  padding: 12px 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  z-index: 1000;
  font-family: Georgia, 'Times New Roman', Times, serif;
}

.notification-content {
  display: flex;
  align-items: center;
  gap: 10px;
}

.pulse {
  display: inline-block;
  width: 10px;
  height: 10px;
  background-color: #4CAF50;
  border-radius: 50%;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.7);
  }
  
  70% {
    transform: scale(1);
    box-shadow: 0 0 0 10px rgba(76, 175, 80, 0);
  }
  
  100% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(76, 175, 80, 0);
  }
}
</style>