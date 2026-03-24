<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { RecordService } from '@/_services/RecordService';
import { getKidById } from '@/_services/KidService';
import type { Record } from '@/_models/Record';
import type { Kid } from '@/_models/Kid';
import { formatDate, formatHours, escapeHtml } from '@/_utils/dateUtils';
import html2pdf from 'html2pdf.js';

const route = useRoute();
const kidId = route.params.id;

const records = ref<Record[]>([]);
const allRecords = ref<Record[]>([]); // Tous les relevés sans filtre
const kid = ref<Kid | null>(null);
const errorMessage = ref<string | null>(null);
const isLoading = ref(true);
const isPdfGenerating = ref(false);

// Date filters
const startDate = ref('');
const endDate = ref('');
const showFilters = ref(false);

// Fonction pour filtrer les relevés par date
const filterRecords = () => {
  if (!startDate.value && !endDate.value) {
    records.value = [...allRecords.value];
    return;
  }

  records.value = allRecords.value.filter(record => {
    const recordDate = record.date ? new Date(record.date) : null;
    if (!recordDate) return false;

    let matchesStart = true;
    let matchesEnd = true;

    if (startDate.value) {
      const start = new Date(startDate.value);
      matchesStart = recordDate >= start;
    }

    if (endDate.value) {
      const end = new Date(endDate.value);
      matchesEnd = recordDate <= end;
    }

    return matchesStart && matchesEnd;
  });
};

// Surveiller les changements de dates
watch([startDate, endDate], () => {
  filterRecords();
});

// Statistiques calculées
const totalHours = computed(() => {
  return records.value.reduce((total, record) => {
    const hours = typeof record.amount_hours === 'string' 
      ? parseFloat(record.amount_hours) 
      : record.amount_hours || 0;
    return total + hours;
  }, 0);
});

const averageHoursPerDay = computed(() => {
  if (records.value.length === 0) return 0;
  return totalHours.value / records.value.length;
});

const mostFrequentHour = computed(() => {
  const hours = records.value.map(record => record.drop_hour);
  return hours.sort((a, b) =>
    hours.filter(h => h === a).length - hours.filter(h => h === b).length
  ).pop();
});

// Fonction pour charger les données
const loadKidAndRecords = async () => {
  try {
    isLoading.value = true;
    const [kidData, recordsData] = await Promise.all([
      getKidById(Number(kidId)),
      RecordService.getKidRecords(Number(kidId))
    ]);
    
    kid.value = kidData;
    allRecords.value = recordsData.sort((a: Record, b: Record) => 
      new Date(b.date || '').getTime() - new Date(a.date || '').getTime()
    );
    
    // Initialiser les relevés filtrés avec tous les relevés
    records.value = [...allRecords.value];
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Une erreur est survenue.';
  } finally {
    isLoading.value = false;
  }
};

// Fonction pour télécharger les relevés filtrés en PDF
const downloadFilteredRecordsAsPdf = () => {
  if (!kid.value || records.value.length === 0) return;
  
  isPdfGenerating.value = true;
  
  // Créer un élément temporaire pour le PDF
  const pdfContent = document.createElement('div');
  pdfContent.className = 'pdf-content';
  
  // Ajouter le titre et les informations de l'enfant
  let innerHTML = `
    <div class="pdf-container">
      <header class="pdf-header">
        <h1>Relevés de présence</h1>
        <p class="pdf-kid-name">Enfant : ${escapeHtml(kid.value.first_name)}</p>
        <p class="pdf-period">
          Période : ${startDate.value ? formatDate(startDate.value) : 'Début'} - 
          ${endDate.value ? formatDate(endDate.value) : 'Fin'}
        </p>
      </header>
      
      <section class="pdf-stats">
        <h2>Résumé</h2>
        <div class="pdf-stats-grid">
          <div class="pdf-stat-item">
            <h3>Total des heures</h3>
            <p>${formatHours(totalHours.value)}</p>
          </div>
          <div class="pdf-stat-item">
            <h3>Moyenne par jour</h3>
            <p>${formatHours(averageHoursPerDay.value)}</p>
          </div>
          <div class="pdf-stat-item">
            <h3>Nombre de relevés</h3>
            <p>${records.value.length}</p>
          </div>
        </div>
      </section>
      
      <section class="pdf-records">
        <h2>Liste des relevés</h2>
        <table class="pdf-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Arrivée</th>
              <th>Départ</th>
              <th>Durée</th>
              <th>Annotation</th>
            </tr>
          </thead>
          <tbody>
  `;
  
  // Ajouter chaque relevé au tableau
  records.value.forEach(record => {
    innerHTML += `
      <tr>
        <td>${formatDate(record.date || '')}</td>
        <td>${record.drop_hour}</td>
        <td>${record.pick_up_hour || 'En cours'}</td>
        <td>${record.amount_hours ? formatHours(record.amount_hours) : 'En cours'}</td>
        <td>${escapeHtml(record.annotation || '-')}</td>
      </tr>
    `;
  });
  
  // Fermer le tableau et les sections
  innerHTML += `
          </tbody>
        </table>
      </section>
    </div>
  `;
  
  pdfContent.innerHTML = innerHTML;
  
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
    
    .pdf-kid-name, .pdf-period {
      font-size: 16px;
      margin: 5px 0;
    }
    
    .pdf-stats {
      margin-bottom: 30px;
    }
    
    .pdf-stats h2, .pdf-records h2 {
      font-size: 18px;
      margin-bottom: 15px;
      border-bottom: 1px solid #ccc;
      padding-bottom: 5px;
    }
    
    .pdf-stats-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-bottom: 20px;
    }
    
    .pdf-stat-item {
      background: #f5f5f5;
      padding: 15px;
      border-radius: 5px;
      text-align: center;
    }
    
    .pdf-stat-item h3 {
      font-size: 14px;
      margin-bottom: 5px;
      color: #666;
    }
    
    .pdf-stat-item p {
      font-size: 16px;
      font-weight: bold;
      margin: 0;
    }
    
    .pdf-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .pdf-table th, .pdf-table td {
      padding: 10px;
      border: 1px solid #ddd;
      text-align: left;
    }
    
    .pdf-table th {
      background-color: #f5f5f5;
      font-weight: bold;
    }
    
    .pdf-table tr:nth-child(even) {
      background-color: #f9f9f9;
    }
  `;
  
  pdfContent.appendChild(style);
  
  // Configurer les options PDF
  const period = startDate.value || endDate.value
    ? `${startDate.value ? formatDate(startDate.value) : 'debut'}_${endDate.value ? formatDate(endDate.value) : 'fin'}`
    : 'tous';
  
  const options = {
    margin: 10,
    filename: `releves_${kid.value.first_name}_${period}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true },
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

// Reset des filtres
const resetFilters = () => {
  startDate.value = '';
  endDate.value = '';
  records.value = [...allRecords.value];
};

onMounted(() => {
  loadKidAndRecords();
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

    <div v-else-if="kid" class="records-view">
      <header class="records-header">
        <h1>Historique des relevés</h1>
        <p class="kid-name">{{ kid.first_name }}</p>
      </header>
      
      <!-- Filtre de date et bouton de téléchargement -->
      <section class="filters-section">
        <button 
          @click="showFilters = !showFilters" 
          class="toggle-filters-btn"
        >
          <font-awesome-icon :icon="showFilters ? 'fa-solid fa-filter-circle-xmark' : 'fa-solid fa-filter'" />
          {{ showFilters ? 'Masquer les filtres par date' : 'Afficher les filtres par date' }}
        </button>
        
        <div v-if="showFilters" class="filters-container">
          <div class="date-filters">
            <div class="filter-group">
              <label for="start-date">Début de la période souhaitée</label>
              <input 
                type="date" 
                id="start-date" 
                v-model="startDate"
                class="date-input"
              >
            </div>
            
            <div class="filter-group">
              <label for="end-date">Fin de la periode souhaitée</label>
              <input 
                type="date" 
                id="end-date" 
                v-model="endDate"
                class="date-input"
              >
            </div>
          </div>
          
          <div class="filter-actions">
            <button @click="resetFilters" class="reset-btn">
              <font-awesome-icon icon="fa-solid fa-rotate-left" />
              Réinitialiser
            </button>
            
            <button 
              @click="downloadFilteredRecordsAsPdf"
              class="download-btn"
              :disabled="isPdfGenerating || records.length === 0"
            >
              <font-awesome-icon icon="fa-solid fa-file-pdf" />
              <span v-if="isPdfGenerating">Génération du PDF...</span>
              <span v-else>Télécharger les relevés filtrés</span>
            </button>
          </div>
        </div>
      </section>
      
      <section class="stats-section">
        <div class="stats-grid">
          <div class="stat-card">
            <h3>Total des heures</h3>
            <p>{{ formatHours(totalHours) }}</p>
          </div>
          <div class="stat-card">
            <h3>Moyenne par jour</h3>
            <p>{{ formatHours(averageHoursPerDay) }}</p>
          </div>
          <div class="stat-card">
            <h3>Heure d'arrivée habituelle</h3>
            <p>{{ mostFrequentHour || 'N/A' }}</p>
          </div>
          <div class="stat-card">
            <h3>Nombre de relevés</h3>
            <p>{{ records.length }}</p>
          </div>
        </div>
      </section>
      
      <section class="records-section">
        <h2>
          Liste des relevés
          <span v-if="startDate || endDate" class="filtered-label">
            (filtrés)
          </span>
        </h2>
        
        <div v-if="records.length === 0" class="no-records">
          Aucun relevé trouvé pour cette période.
        </div>
        
        <div v-else class="records-list">
          <router-link 
            v-for="record in records" 
            :key="record.id"
            :to="`/recordDetail/${record.id}`"
            class="record-card"
          >
            <div class="record-date">
              <font-awesome-icon icon="fa-solid fa-calendar" class="icon" />
              {{ formatDate(record.date || '') }}
            </div>
            <div class="record-times">
              <div class="time-item">
                <font-awesome-icon icon="fa-solid fa-arrow-right" class="icon" />
                {{ record.drop_hour }}
              </div>
              <div class="time-item">
                <font-awesome-icon icon="fa-solid fa-arrow-left" class="icon" />
                {{ record.pick_up_hour || 'En cours' }}
              </div>
            </div>
            <div class="record-duration">
              <font-awesome-icon icon="fa-solid fa-clock" class="icon" />
              {{ record.amount_hours ? formatHours(record.amount_hours) : 'En cours' }}
            </div>
          </router-link>
        </div>
      </section>
      
      <div class="actions">
        <router-link :to="`/kids/${kid.id}`" class="back-button">
          <font-awesome-icon icon="fa-solid fa-arrow-left" />
          Retour au profil
        </router-link>
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

.records-view {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.records-header {
  text-align: center;
  margin-bottom: 2rem;
}

.records-header h1 {
  font-size: 2.5rem;
  color: #333;
  margin-bottom: 0.5rem;
}

.kid-name {
  color: #666;
  font-style: italic;
  font-size: 1.2rem;
}

/* Styles pour la section de filtres */
.filters-section {
  background: #f5f5f5;
  border-radius: 20px;
  padding: 1.5rem;
  border: 2px solid #c3c6ce;
  transition: all 0.3s ease;
}

.filters-section:hover {
  border-color: #78BB99;
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.25);
}

.toggle-filters-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #358E9D;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 0.8rem 1.2rem;
  cursor: pointer;
  transition: background-color 0.2s;
  font-weight: 500;
}

.toggle-filters-btn:hover {
  background: #2d7a87;
}

.filters-container {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #ddd;
}

.date-filters {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.filter-group label {
  margin-bottom: 0.5rem;
  color: #666;
  font-size: 0.9rem;
}

.date-input {
  padding: 0.8rem;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 1rem;
}

.filter-actions {
  display: flex;
  justify-content: space-between;
}

.reset-btn, .download-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.8rem 1.2rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 500;
}

.reset-btn {
  background: #f0f0f0;
  color: #666;
}

.reset-btn:hover {
  background: #e0e0e0;
}

.download-btn {
  background: #E26D5C;
  color: white;
}

.download-btn:hover:not(:disabled) {
  background: #c85a4a;
}

.download-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.filtered-label {
  font-size: 0.9rem;
  color: #666;
  font-style: italic;
}

.no-records {
  text-align: center;
  padding: 2rem;
  background: white;
  border-radius: 10px;
  font-style: italic;
  color: #666;
}

.stats-section {
  background: #f5f5f5;
  border-radius: 20px;
  padding: 2rem;
  border: 2px solid #c3c6ce;
  transition: all 0.3s ease;
}

.stats-section:hover {
  border-color: #78BB99;
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.25);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 15px;
  text-align: center;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-card h3 {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.stat-card p {
  color: #333;
  font-size: 1.5rem;
  font-weight: bold;
  margin: 0;
}

.records-section {
  background: #f5f5f5;
  border-radius: 20px;
  padding: 2rem;
  border: 2px solid #c3c6ce;
}

.records-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1.5rem;
}

.record-card {
  display: grid;
  grid-template-columns: 1fr 2fr 1fr;
  align-items: center;
  padding: 1.2rem;
  background: white;
  border-radius: 15px;
  text-decoration: none;
  color: inherit;
  transition: all 0.3s ease;
  border: 1px solid transparent;
}

.record-card:hover {
  transform: translateX(5px);
  border-color: #78BB99;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.record-date {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #666;
}

.record-times {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.time-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.record-duration {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
  justify-content: flex-end;
}

.icon {
  color: #358E9D;
}

.actions {
  display: flex;
  justify-content: center;
  margin-top: 2rem;
}

.back-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.8rem 1.5rem;
  background-color: #358E9D;
  color: white;
  border: none;
  border-radius: 6px;
  text-decoration: none;
  transition: background-color 0.2s;
}

.back-button:hover {
  background-color: #2d7a87;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.error-card {
  background: #fee;
  color: #c00;
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .record-card {
    grid-template-columns: 1fr;
    gap: 1rem;
    text-align: center;
  }

  .record-date, .record-times, .record-duration {
    justify-content: center;
  }
  
  .date-filters {
    flex-direction: column;
    gap: 1rem;
  }
  
  .filter-actions {
    flex-direction: column;
    gap: 1rem;
  }
  
  .reset-btn, .download-btn {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 425px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>