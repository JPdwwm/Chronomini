import { defineStore } from 'pinia';
import { ref, watch } from 'vue';
import RecordService from '@/_services/RecordService';
import { useAuthStore } from './auth';
import type { Record } from '@/_models/Record';

export const useRecordStore = defineStore('record', () => {
  const activeRecord = ref<any>(null);
  const authStore = useAuthStore(); // On récupère le store d'authentification

  watch(
    () => authStore.isAuthenticated,
    (isAuthenticated) => {
      if (!isAuthenticated) {
        // Si déconnecté, réinitialiser l'enregistrement actif
        activeRecord.value = null;
      } else {
        // Si connecté, vérifier s'il y a un enregistrement actif
        checkActiveRecord();
      }
    }
  );

  const checkActiveRecord = async () => {
    // Vérifier si l'utilisateur est connecté avant de récupérer les enregistrements en cours
    if (!authStore.isAuthenticated) {
      activeRecord.value = null;
      return;
    }

    try {
      const response = await RecordService.getMyRecords();
      const record = response.data.find(
        (record: Record) => !record.pick_up_hour
      );
      activeRecord.value = record || null;
    } catch (error) {
      activeRecord.value = null;
    }
  };

  return {
    activeRecord,
    checkActiveRecord
  };
});