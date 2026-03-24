<script setup lang="ts">
import { ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const activeModal = ref<string | null>(null);
const demoTimer = ref<string>('0h 0min 0s');
const isDemoTracking = ref(false);
const isModalOpen = ref(false);
const isClicked = ref(false);

const startDemoTimer = () => {
  isClicked.value = true; 
  isDemoTracking.value = !isDemoTracking.value;
  
  if (isDemoTracking.value) {
    let seconds = 0;
    const interval = setInterval(() => {
      if (!isDemoTracking.value) {
        clearInterval(interval);
        return;
      }
      
      seconds++;
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      const secs = seconds % 60;
      demoTimer.value = `${hours}h ${minutes}min ${secs}s`;
    }, 1000);

    // Arrêter après 10 secondes pour la démo
    setTimeout(() => {
      clearInterval(interval);
      isDemoTracking.value = false;
      demoTimer.value = '0h 0min 0s';
    }, 10000);
  } else {
    demoTimer.value = '0h 0min 0s';
  }

  setTimeout(() => {
    isClicked.value = false;
  }, 1000);
};

const openModal = (modalId: string) => {
  activeModal.value = modalId;
  isModalOpen.value = true;
  isDemoTracking.value = false;
  demoTimer.value = '0h 0min 0s';
};

const closeModal = () => {
  activeModal.value = null;
  isModalOpen.value = false;
  isDemoTracking.value = false;
  demoTimer.value = '0h 0min 0s';
};
</script>

<template>
    <section id="gestion-enfants" class="section-demo">
    <h2>Premiers pas avec ChronoMini</h2>
    <div class="section-content">
      <p>Avant de pouvoir commencer à enregistrer les heures de garde, vous devez d'abord ajouter un enfant dans l'application. Cette section vous guide à travers les premières étapes pour configurer votre compte.</p>
      <p>Suivez le guide pour apprendre comment ajouter un enfant à votre compte et commencer à gérer ses heures de garde.</p>
    </div>
    <button class="demo-btn" @click="openModal('startGuide')">
      Guide de démarrage
    </button>
    
    <div v-if="activeModal === 'startGuide'" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content scrollable">
        <button class="modal-close" @click="closeModal">
          <FontAwesomeIcon icon="fa-solid fa-times" />
        </button>
        
        <h3>Comment démarrer avec ChronoMini</h3>
        
        <div class="start-guide">
          <div class="guide-step">
            <div class="step-number">1</div>
            <div class="step-content">
              <h4>Accéder à la section Enfants</h4>
              <p>Depuis la barre de navigation, cliquez sur l'onglet <strong>Enfants</strong> pour accéder à la gestion des enfants.</p>
            </div>
          </div>
          
          <div class="guide-step">
            <div class="step-number">2</div>
            <div class="step-content">
              <h4>Ajouter un nouvel enfant</h4>
              <p>Sur la page des enfants, cliquez sur la carte <strong>Ajouter un enfant</strong> pour créer un nouveau profil.</p>
              <div class="step-image">
                <div class="card add-card guide-card">
                  <div class="card-details">
                    <FontAwesomeIcon icon="fa-solid fa-plus" class="add-icon" />
                    <h3 class="text-title">Ajouter un enfant</h3>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="guide-step">
            <div class="step-number">3</div>
            <div class="step-content">
              <h4>Remplir les informations</h4>
              <p>Renseignez les informations demandées concernant l'enfant (prénom, date de naissance) et validez.</p>
            </div>
          </div>
          
          <div class="guide-step">
            <div class="step-number">4</div>
            <div class="step-content">
              <h4>Accéder à la gestion des heures</h4>
              <p>Une fois l'enfant créé, cliquez sur le bouton <strong>Gestion</strong> sur sa carte pour commencer à enregistrer ses heures de garde.</p>
              <div class="step-image">
                <div class="card guide-card">
                  <div class="card-details">
                    <h3 class="text-title">Lucas</h3>
                    <p class="text-body"><strong>Né(e) le:</strong> 12/03/2022</p>
                  </div>
                  <span class="card-button visible">Gestion</span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="guide-step">
            <div class="step-number">5</div>
            <div class="step-content">
              <h4>Commencer le pointage</h4>
              <p>Vous pouvez maintenant utiliser le bouton de pointage pour enregistrer les heures de dépose et de récupération de l'enfant.</p>
            </div>
          </div>
        </div>
        
        <div class="guide-notes">
          <p><strong>Bon à savoir :</strong> Vous pouvez ajouter plusieurs enfants à votre compte si vous êtes parent, ou gérer plusieurs enfants si vous êtes assistante maternelle.</p>
          <p><strong>Besoin d'aide ?</strong> N'hésitez pas à consulter les autres sections de cette page d'aide pour en savoir plus sur les fonctionnalités de ChronoMini.</p>
        </div>
      </div>
    </div>
  </section>
<section id="gestion-heures" class="section-demo">
  <h2>Gestion des heures de garde</h2>
  <div class="section-content">
    <p>Le décompte des heures de garde est une tâche qui peut s'avérer fastidieuse en fin de mois, particulièrement en cas d'oubli ou de doute sur certaines journées. Notre application simplifie ce processus en vous permettant de valider les heures en temps réel.</p>
    <p>Plus besoin de noter les heures sur un carnet ou de faire des calculs complexes en fin de mois. Un simple clic vous permet d'enregistrer les horaires d'arrivée et de départ de l'enfant.</p>
  </div>
  <button class="demo-btn" @click="openModal('timer')">
    Voir la démonstration
  </button>
    <div v-if="activeModal === 'timer'" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <button class="modal-close" @click="closeModal">
          <FontAwesomeIcon icon="fa-solid fa-times" />
        </button>
        
        <h3>Démonstration du pointage</h3>
        
        <div class="demo-timer">
          <div class="container-btn">
            <button 
              class="btn" 
              :class="{ active: isClicked }" 
              @click="startDemoTimer"
            >
              <span class="icon">
                <FontAwesomeIcon :icon="!isDemoTracking ? 'fa-solid fa-play' : 'fa-solid fa-stop'" />
              </span>
              <span class="text">
                {{ !isDemoTracking ? 'Valider la dépose' : 'Valider la récupération' }}
              </span>
            </button>
          </div>
          
          <div class="controls-container">
            <div class="timer-container">
              <svg 
                class="progress-circle" 
                :class="{ active: isDemoTracking }" 
                viewBox="0 0 150 150"
              >
                <circle class="bg" cx="75" cy="75" r="70" />
                <circle class="progress" cx="75" cy="75" r="70" />
              </svg>
              <div class="timer">
                {{ demoTimer }}
              </div>
            </div>
          </div>
        </div>

        <div class="demo-info">
          <p>Cette démonstration simule le fonctionnement du système de pointage :</p>
          <ol>
            <li>Cliquez sur "Valider la dépose" pour démarrer le compteur</li>
            <li>Le temps s'écoulera pendant 10 secondes pour la démonstration</li>
            <li>Le compteur s'arrêtera automatiquement</li>
          </ol>
          <p><em>Note : Dans l'application réelle, le compteur continuera jusqu'à ce que vous validiez la récupération.</em></p>
        </div>
      </div>
    </div>
  </section>

  <section id="telecharger-releves" class="section-demo">
  <h2>Téléchargement des relevés</h2>
  <div class="section-content">
    <p>Les utilisateurs ont accès à un système complet de gestion des relevés d'heures. Chaque mois, vous pouvez télécharger des fiches récapitulatives détaillant l'ensemble des heures de garde effectuées.</p>
    <p>L'historique complet des données reste accessible à tout moment, garantissant une parfaite traçabilité des informations enregistrées dans l'application. Cette fonctionnalité est particulièrement utile pour le suivi administratif et la gestion de la relation parent-assistante maternelle.</p>
  </div>
  <button class="demo-btn" @click="openModal('download')">
    Voir la démonstration
  </button>
    <div v-if="activeModal === 'download'" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content scrollable">
        <button class="modal-close" @click="closeModal">
          <FontAwesomeIcon icon="fa-solid fa-times" />
        </button>
        
        <h3>Démonstration du téléchargement</h3>
        <div class="demo-download">
          <div class="filters-container">
            <div class="date-filters">
              <div class="filter-group">
                <label for="demo-start-date">Début de la période</label>
                <input 
                  type="date" 
                  id="demo-start-date" 
                  class="date-input"
                >
              </div>
              
              <div class="filter-group">
                <label for="demo-end-date">Fin de la période</label>
                <input 
                  type="date" 
                  id="demo-end-date" 
                  class="date-input"
                >
              </div>
            </div>
            
            <div class="demo-stats">
              <div class="stats-preview">
                <div class="stat-preview-card">
                  <h4>Total des heures</h4>
                  <p>42h 30min</p>
                </div>
                <div class="stat-preview-card">
                  <h4>Moyenne par jour</h4>
                  <p>8h 30min</p>
                </div>
                <div class="stat-preview-card">
                  <h4>Nombre de relevés</h4>
                  <p>5</p>
                </div>
              </div>
            </div>
            
            <div class="filter-actions">
              <button class="reset-btn">
                <FontAwesomeIcon icon="fa-solid fa-rotate-left" />
                Réinitialiser
              </button>
              
              <button class="download-btn">
                <FontAwesomeIcon icon="fa-solid fa-file-pdf" />
                Télécharger les relevés
              </button>
            </div>
          </div>
          
          <div class="demo-records-preview">
            <h4>Aperçu des relevés</h4>
            <div class="records-list-preview">
              <div class="record-card-preview">
                <div class="record-date">
                  <FontAwesomeIcon icon="fa-solid fa-calendar" class="icon" />
                  20/04/2025
                </div>
                <div class="record-times">
                  <div class="time-item">
                    <FontAwesomeIcon icon="fa-solid fa-arrow-right" class="icon" />
                    8:30
                  </div>
                  <div class="time-item">
                    <FontAwesomeIcon icon="fa-solid fa-arrow-left" class="icon" />
                    17:00
                  </div>
                </div>
                <div class="record-duration">
                  <FontAwesomeIcon icon="fa-solid fa-clock" class="icon" />
                  8h 30min
                </div>
              </div>
              
              <div class="record-card-preview">
                <div class="record-date">
                  <FontAwesomeIcon icon="fa-solid fa-calendar" class="icon" />
                  19/04/2025
                </div>
                <div class="record-times">
                  <div class="time-item">
                    <FontAwesomeIcon icon="fa-solid fa-arrow-right" class="icon" />
                    9:00
                  </div>
                  <div class="time-item">
                    <FontAwesomeIcon icon="fa-solid fa-arrow-left" class="icon" />
                    17:30
                  </div>
                </div>
                <div class="record-duration">
                  <FontAwesomeIcon icon="fa-solid fa-clock" class="icon" />
                  8h 30min
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="demo-info">
          <p>Cette démonstration simule le téléchargement des relevés :</p>
          <ol>
            <li>Sélectionnez une période en définissant des dates de début et de fin</li>
            <li>Visualisez les statistiques calculées automatiquement</li>
            <li>Téléchargez le relevé au format PDF pour l'imprimer ou le partager</li>
          </ol>
          <p><em>Note : Dans l'application réelle, vous pourrez télécharger les relevés complets contenant toutes les informations sur les heures de garde.</em></p>
        </div>
      </div>
    </div>
  </section>

  <section id="fiabilite-precision" class="section-demo">
  <h2>Fiabilité et précision</h2>
  <div class="section-content">
    <p>La précision des calculs est essentielle pour éviter toute perte financière, que ce soit pour les parents ou l'assistante maternelle. Notre application assure un calcul exact des heures de garde, éliminant les risques d'erreurs humaines dans le décompte.</p>
    <p>Le risque d'oubli lors du pointage de dépose et de récupération restant présent, nous mettons en place un systéme d'annotation afin qu'une mention ou des précisions puissent être laissées sur un relevé.</p>
  </div>
  <button class="demo-btn" @click="openModal('reliability')">
    Voir les détails
  </button>
    <div v-if="activeModal === 'reliability'" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <button class="modal-close" @click="closeModal">
          <FontAwesomeIcon icon="fa-solid fa-times" />
        </button>
        
        <h3>Fiabilité et précision du système</h3>
        <div class="reliability-info">
          <div class="info-card">
            <FontAwesomeIcon icon="fa-solid fa-calculator" class="info-icon" />
            <h4>Calcul automatique</h4>
            <p>Précision à la minute près</p>
          </div>
          <div class="info-card">
            <FontAwesomeIcon icon="fa-solid fa-history" class="info-icon" />
            <h4>Historique complet</h4>
            <p>Traçabilité et filtre appliquable</p>
          </div>
          <div class="info-card">
            <FontAwesomeIcon icon="fa-solid fa-pen" class="info-icon" />
            <h4>Système d'annotations</h4>
            <p>En cas d'oublie dans le pointage</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.start-guide {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 1rem 0;
}

.guide-step {
  display: flex;
  gap: 1rem;
  position: relative;
}

.step-number {
  width: 36px;
  height: 36px;
  background-color: #358E9D;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.2rem;
  flex-shrink: 0;
}

.step-content {
  flex: 1;
}

.step-content h4 {
  color: #358E9D;
  margin-bottom: 0.5rem;
}

.step-image {
  margin-top: 0.75rem;
  border-radius: 8px;
  overflow: hidden;
  text-align: center;
}

.guide-img {
  max-width: 100%;
  height: auto;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
}

.guide-notes {
  margin-top: 2rem;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 8px;
  border-left: 4px solid #78BB99;
}

.guide-notes p {
  margin-bottom: 0.75rem;
}

.guide-notes p:last-child {
  margin-bottom: 0;
}

/* Style pour la carte d'exemple */
.guide-card {
  width: 190px;
  height: 10rem;
  border-radius: 20px;
  background: #f5f5f5;
  position: relative;
  padding: 1.2rem;
  border: 2px solid #c3c6ce;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.add-card.guide-card {
  border: 2px dashed #c3c6ce;
}

.add-card.guide-card .card-details {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  gap: 10px;
}

.add-card.guide-card .add-icon {
  font-size: 2em;
  color: #008bf8;
}

.card-button.visible {
  opacity: 1;
  transform: translate(-50%, 10%);;
  width: 60%;
  border-radius: 1rem;
  border: none;
  background-color: #78BB99;
  color: #fff;
  font-size: 1rem;
  padding: 0.5rem 1rem;
  position: absolute;
  left: 50%;
  bottom: 0;
  text-align: center;
}
.section-demo {
  padding: 2rem;
  background-color: #f5f5f5;
  margin-bottom: 2rem;
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
  background-color: white;
  padding: 1.5rem;
  border-radius: 8px;
  max-width: 600px;
  width: 90%;
  position: relative;
  max-height: 85vh;
}

.modal-content.scrollable {
  overflow-y: auto;
}

.modal-close {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: #666;
  z-index: 2;
}

.demo-btn {
  background-color: #358E9D;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.3s;
}

.demo-btn:hover {
  background-color: #2a7180;
}

/* Styles du bouton timer */
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
  cursor: pointer;
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
  z-index: 2;
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

.btn.active:after {
  transform: translateX(300px) skewX(30deg);
}

/* Styles du timer */
.demo-timer {
  width: 100%;
  padding: 1.5rem 0;
}

.container-btn {
  width: 100%;
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.timer-container {
  position: relative;
  width: 160px;
  height: 160px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
}

.progress-circle {
  position: absolute;
  width: 120px;
  height: 120px;
  transform: rotate(-90deg);
}

.progress-circle circle {
  fill: none;
  stroke-width: 10;
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

.bg {
  stroke: #e0e0e0;
}

.timer {
  position: absolute;
  font-size: 1rem;
  font-weight: bold;
  color: #333;
  z-index: 1;
}

@keyframes progressAnimation {
  from {
    stroke-dashoffset: 440;
  }
  to {
    stroke-dashoffset: 0;
  }
}

/* Styles pour la démo de téléchargement */
.demo-download {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 0.5rem 0;
}

.filters-container {
  background: #f5f5f5;
  border-radius: 12px;
  padding: 1rem;
  border: 1px solid #e0e0e0;
}

.date-filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.filter-group label {
  margin-bottom: 0.3rem;
  color: #666;
  font-size: 0.85rem;
}

.date-input {
  padding: 0.6rem;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 0.9rem;
}

.demo-stats {
  margin: 1rem 0;
}

.stats-preview {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
}

.stat-preview-card {
  background: white;
  padding: 0.75rem;
  border-radius: 8px;
  text-align: center;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.stat-preview-card h4 {
  color: #666;
  font-size: 0.8rem;
  margin-bottom: 0.3rem;
}

.stat-preview-card p {
  color: #333;
  font-size: 1rem;
  font-weight: bold;
  margin: 0;
}

.filter-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 1rem;
}

.reset-btn, .download-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 500;
  font-size: 0.9rem;
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

.download-btn:hover {
  background: #c85a4a;
}

.demo-records-preview {
  background: #f5f5f5;
  border-radius: 12px;
  padding: 1rem;
  border: 1px solid #e0e0e0;
}

.demo-records-preview h4 {
  margin-bottom: 0.75rem;
  color: #358E9D;
  font-size: 0.9rem;
}

.records-list-preview {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.record-card-preview {
  display: grid;
  grid-template-columns: 1fr 2fr 1fr;
  align-items: center;
  padding: 0.75rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  font-size: 0.9rem;
}

.record-date, .record-times, .record-duration {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.record-times {
  flex-direction: column;
}

.record-duration {
  justify-content: flex-end;
  font-weight: bold;
}

.icon {
  color: #358E9D;
}

/* Styles pour la section de fiabilité */
.reliability-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.info-card {
  padding: 1.25rem;
  background-color: #f8f9fa;
  border-radius: 8px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.info-icon {
  font-size: 1.75rem;
  color: #358E9D;
  margin-bottom: 0.75rem;
}

.demo-info {
  margin-top: 1.5rem;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 8px;
  font-size: 0.9rem;
}

.demo-info ol {
  margin: 0.75rem 0 0.75rem 1.5rem;
}

.demo-info li {
  margin: 0.4rem 0;
  color: #666;
}

h2, h3, h4 {
  color: #333;
  margin-bottom: 0.75rem;
}

h2 {
  font-size: 1.5rem;
}

h3 {
  font-size: 1.25rem;
}

p {
  color: #666;
  line-height: 1.5;
}

.section-content {
  margin: 1.5rem 0;
}

.section-content p {
  margin-bottom: 1rem;
  line-height: 1.6;
  color: #4A4A4A;
  font-size: 1.1rem;
}

.section-demo h2 {
  color: #358E9D;
  font-size: 1.75rem;
  margin-bottom: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
  .guide-step {
    flex-direction: column;
  }
  
  .step-number {
    margin-bottom: 0.5rem;
  }
  
  .guide-card {
    width: 75%;
    max-width: 180px;
  }
  .stats-preview {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .record-card-preview {
    grid-template-columns: 1fr;
    gap: 0.6rem;
    text-align: center;
  }
  
  .record-date, .record-times, .record-duration {
    justify-content: center;
  }
  
  .date-filters {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .filter-actions {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .reset-btn, .download-btn {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 500px) {
  .stats-preview {
    grid-template-columns: 1fr;
  }
  
  .modal-content {
    padding: 1rem;
    width: 95%;
    max-height: 100%;
  }
}
</style>