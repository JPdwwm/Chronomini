import { defineStore } from 'pinia';
import { ref } from 'vue';
import RecordService from '@/_services/RecordService';

export const useBreakStore = defineStore('breaks', () => {
  const activeBreaks = ref<Record<number, boolean>>({});
  const isPendingSync = ref<number[]>([]);
  
  // Synchroniser avec le serveur
  async function syncWithServer(recordId: number) {
    if (isPendingSync.value.includes(recordId)) return activeBreaks.value[recordId] || false;
    
    isPendingSync.value.push(recordId);
    try {
      const { hasActiveBreak } = await RecordService.checkActiveBreak(recordId);
      activeBreaks.value[recordId] = hasActiveBreak;
      return hasActiveBreak;
    } catch (error) {
      return activeBreaks.value[recordId] || false;
    } finally {
      isPendingSync.value = isPendingSync.value.filter(id => id !== recordId);
    }
  }
  
  // Démarrer une pause
  async function startBreak(recordId: number) {
    try {
      await RecordService.startBreak(recordId);
      activeBreaks.value[recordId] = true;
      return true;
    } catch (error) {
      return false;
    }
  }
  
  // Terminer une pause
  async function endBreak(recordId: number) {
    try {
      await RecordService.endBreak(recordId);
      activeBreaks.value[recordId] = false;
      return true;
    } catch (error) {
      return false;
    }
  }
  
  // Vérifier si une pause est active
  function isBreakActive(recordId: number) {
    return activeBreaks.value[recordId] || false;
  }
  
  return { 
    activeBreaks, 
    syncWithServer, 
    startBreak, 
    endBreak, 
    isBreakActive 
  };
}, {
  persist: {
    storage: localStorage,
    pick: ['activeBreaks']
  }
});