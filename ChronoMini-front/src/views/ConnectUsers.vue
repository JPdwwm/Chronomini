<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import type { User } from '@/_models/User';
import type { Kid } from '@/_models/Kid';
import { connectUsers, getReceivedConnectionRequests, getSentConnectionRequests, acceptConnectionRequest, declineConnectionRequest, getConnectedUsers, disconnectUser, mergeKids, checkDuplicatesWithUser, shareKidWithPartner
} from '@/_services/ConnectionService';
import type { ConnectionRequest } from '@/_models/ConnectionRequest';
import LoadingIcon from '@/components/LoadingIcon.vue';
import DuplicateKidsModal from '@/components/DuplicateKidsModal.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import KidSharingModal from '@/components/KidSharingModal.vue';
import { getKids } from '@/_services/KidService';

const linkCode = ref('');
const isSubmitting = ref(false);
const errorMessage = ref('');
const connectionRequest = ref<ConnectionRequest | null>(null);
const receivedRequests = ref<ConnectionRequest[]>([]);
const sentRequests = ref<ConnectionRequest[]>([]);
const isLoading = ref(false);
const activeTab = ref<'connect' | 'received' | 'sent' | 'connected'>('connect');
const potentialDuplicates = ref([]);
const showDuplicatesModal = ref(false);
const connectedUsers = ref<User[]>([]);
const showDisconnectModal = ref(false);
const selectedPartner = ref<User | null>(null);
const disconnectSuccess = ref('');
const disconnectError = ref('');
const userKids = ref<Kid[]>([]);
const showSharingModal = ref(false);
const selectedPartnerForSharing = ref<User | null>(null);
const sharingSuccess = ref('');
const sharingError = ref('');

const submitLinkCode = async () => {
  if (!linkCode.value.trim()) return;
  
  try {
    isSubmitting.value = true;
    errorMessage.value = '';
    
    const response = await connectUsers({ link_code: linkCode.value });
    
    connectionRequest.value = {
      id: response.request.id,
      sender_id: response.request.sender_id,
      receiver_id: response.request.receiver_id,
      status: response.request.status,
      partner: response.receiver
    };
    
    linkCode.value = '';
    await fetchConnectionRequests();
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Une erreur est survenue lors de la tentative de connexion.';
  } finally {
    isSubmitting.value = false;
  }
};

const fetchConnectionRequests = async () => {
  try {
    isLoading.value = true;
    receivedRequests.value = await getReceivedConnectionRequests();
    sentRequests.value = await getSentConnectionRequests();
  } catch (error) {
  } finally {
    isLoading.value = false;
  }
};

const handleAcceptRequest = async (requestId: number) => {
  try {
    const response = await acceptConnectionRequest(requestId);
    
    // Vérifier s'il y a des doublons potentiels
    if (response.potential_duplicates && response.potential_duplicates.length > 0) {
      potentialDuplicates.value = response.potential_duplicates;
      showDuplicatesModal.value = true;
    }
    
    await fetchConnectionRequests();
    await fetchConnectedUsers();
  } catch (error) {
  }
};

const handleDeclineRequest = async (requestId: number) => {
  try {
    await declineConnectionRequest(requestId);
    await fetchConnectionRequests();
  } catch (error) {
  }
};

const pendingSentRequestsCount = computed(() => {
  return sentRequests.value.filter(req => req.status === 'pending').length;
});

const fetchConnectedUsers = async () => {
  try {
    isLoading.value = true;
    connectedUsers.value = await getConnectedUsers();
  } catch (error) {
  } finally {
    isLoading.value = false;
  }
};

// Fonction pour initialiser la déconnexion
const initiateDisconnect = (partner: User) => {
  selectedPartner.value = partner;
  showDisconnectModal.value = true;
};

// Fonction pour confirmer la déconnexion
const confirmDisconnect = async () => {
  if (!selectedPartner.value) return;
  
  try {
    isLoading.value = true;
    await disconnectUser(selectedPartner.value.id as number);
    disconnectSuccess.value = `Vous avez été déconnecté de ${selectedPartner.value.first_name} ${selectedPartner.value.last_name}.`;
    await fetchConnectedUsers();
    showDisconnectModal.value = false;
  } catch (error: any) {
    disconnectError.value = error.response?.data?.message || 'Une erreur est survenue lors de la déconnexion.';
  } finally {
    isLoading.value = false;
  }
};

// Fonction pour vérifier les doublons avec un partenaire existant
const checkDuplicatesWithPartner = async (partner: User) => {
  try {
    isLoading.value = true;
    const response = await checkDuplicatesWithUser(partner.id as number);
    
    if (response.potential_duplicates && response.potential_duplicates.length > 0) {
      potentialDuplicates.value = response.potential_duplicates;
      showDuplicatesModal.value = true;
    } else {
      alert('Aucun doublon potentiel trouvé avec ce partenaire.');
    }
  } catch (error) {
  } finally {
    isLoading.value = false;
  }
};

// Fonction pour ouvrir le modal de partage d'enfants
const initiateKidSharing = async (partner: User) => {
  selectedPartnerForSharing.value = partner;
  try {
    isLoading.value = true;
    const response = await getKids();
    
    if (Array.isArray(response)) {
      userKids.value = response;
    } else if (response && Array.isArray(response.kids)) {
      userKids.value = response.kids;
    } else {
      userKids.value = [];
    }
    
    showSharingModal.value = true;
  } catch (error) {
    userKids.value = [];
    showSharingModal.value = true;
  } finally {
    isLoading.value = false;
  }
};

// Fonction pour partager un enfant avec un partenaire
const shareKidWith = async (kidId: number) => {
  if (!selectedPartnerForSharing.value) return;
  
  sharingError.value = ''; // Réinitialiser le message d'erreur
  
  try {
    isLoading.value = true;
    await shareKidWithPartner({
      kid_id: kidId,
      partner_id: selectedPartnerForSharing.value.id as number
    });
    sharingSuccess.value = `Enfant partagé avec succès avec ${selectedPartnerForSharing.value.first_name} ${selectedPartnerForSharing.value.last_name}.`;
    showSharingModal.value = false;
  } catch (error: any) {
    sharingError.value = error.response?.data?.message || 'Une erreur est survenue lors du partage de l\'enfant.';
  } finally {
    isLoading.value = false;
  }
};

const handleMergeCompleted = () => {
  // Rafraîchir les données si nécessaire
  fetchConnectionRequests();
  fetchConnectedUsers();
};

onMounted(() => {
  fetchConnectionRequests();
  fetchConnectedUsers();
});
</script>

<template>
  <div class="connect-user-container">
    <h1>Mes Partenaires</h1>
    
    <div class="tabs">
      <button 
        @click="activeTab = 'connect'"
        :class="{ active: activeTab === 'connect' }"
      >
        Ajouter un partenaire
      </button>
      <button 
        @click="activeTab = 'received'"
        :class="{ active: activeTab === 'received' }"
      >
        Demandes reçues 
        <span v-if="receivedRequests.length" class="badge">
          {{ receivedRequests.length }}
        </span>
      </button>
      <button 
        @click="activeTab = 'sent'"
        :class="{ active: activeTab === 'sent' }"
      >
        Demandes envoyées
        <span v-if="pendingSentRequestsCount" class="badge">
          {{ pendingSentRequestsCount }}
        </span>
      </button>
      <button 
        @click="activeTab = 'connected'"
        :class="{ active: activeTab === 'connected' }"
      >
        Partenaires connectés
      </button>
    </div>
    <!-- Onglet Ajouter un partenaire -->
    <div v-if="activeTab === 'connect'">
      <div class="info-card">
        <h2>Comment ça marche ?</h2>
        <p>Pour vous connecter avec un autre utilisateur, vous devez utiliser son code de liaison unique.</p>
        <p>Demandez à votre partenaire de vous communiquer son code de liaison personnel, visible dans son profil.</p>
      </div>
      
      <div class="connect-form-card">
        <h2>Entrez le code de liaison</h2>
        <form @submit.prevent="submitLinkCode" class="connect-form">
          <div class="input-group">
            <input 
              v-model="linkCode"
              type="text"
              placeholder="Ex: ABC12"
              maxlength="5"
              class="link-code-input"
            />
            <button 
              type="submit" 
              class="connect-button" 
              :disabled="!linkCode.trim() || isSubmitting"
            >
              <span v-if="isSubmitting">
                <LoadingIcon small />
              </span>
              <span v-else>Envoyer une demande</span>
            </button>
          </div>
          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>
        </form>
      </div>
      <div v-if="connectionRequest" class="success-card">
        <h2>Demande de connexion envoyée</h2>
        <p>Votre demande de connexion a été envoyée à :</p>
        <div class="partner-info">
          <p><strong>Identité :</strong> 
            {{ connectionRequest.partner?.name || 'Partenaire' }}
          </p>
        </div>
        <p class="status-message">En attente de l'acceptation de votre partenaire</p>
      </div>
    </div>
    <!-- Onglet Demandes reçues -->
    <div v-if="activeTab === 'received'" class="requests-section">
      <div v-if="isLoading" class="loading">
        <LoadingIcon />
        <p>Chargement des demandes reçues...</p>
      </div>
      <div v-else-if="receivedRequests.length === 0" class="no-requests">
        <p>Vous n'avez pas de demandes de connexion en attente.</p>
      </div>
      <div v-else>
        <h2>Demandes à traiter</h2>
        <div class="requests-list">
          <div 
            v-for="request in receivedRequests" 
            :key="request.id" 
            class="request-card"
          >
            <div class="request-info">
              <p class="request-from">De <strong>{{ request.sender?.first_name }} {{ request.sender?.last_name }}</strong></p>
            </div>
            <div class="request-actions">
              <button 
                @click="handleAcceptRequest(request.id)"
                class="action-button accept-btn"
              >
                Accepter
              </button>
              <button 
                @click="handleDeclineRequest(request.id)"
                class="action-button decline-btn"
              >
                Refuser
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Onglet Demandes envoyées -->
    <div v-if="activeTab === 'sent'" class="requests-section">
      <div v-if="isLoading" class="loading">
        <LoadingIcon />
        <p>Chargement des demandes envoyées...</p>
      </div>
      <div v-else-if="sentRequests.length === 0" class="no-requests">
        <p>Vous n'avez pas de demandes de connexion en cours.</p>
      </div>
      <div v-else>
        <h2>Vos demandes envoyées</h2>
        <div class="requests-list">
          <div 
            v-for="request in sentRequests" 
            :key="request.id" 
            class="request-card"
          >
            <div class="request-info">
              <p class="request-to">À <strong>{{ request.receiver?.first_name }} {{ request.receiver?.last_name }}</strong></p>
            </div>
            <div class="request-status" :class="request.status">
              {{ request.status === 'pending' ? 'En attente' : 
                 request.status === 'accepted' ? 'Acceptée' : 
                 request.status === 'declined' ? 'Refusée' : request.status }}
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Onglet Partenaires connectés avec les nouvelles fonctionnalités -->
    <div v-if="activeTab === 'connected'" class="requests-section">
      <div v-if="isLoading" class="loading">
        <LoadingIcon />
        <p>Chargement des partenaires connectés...</p>
      </div>
      <div v-else-if="disconnectSuccess" class="success-message">
        <p>{{ disconnectSuccess }}</p>
        <button @click="disconnectSuccess = ''" class="close-btn">×</button>
      </div>
      <div v-else-if="disconnectError" class="error-message">
        <p>{{ disconnectError }}</p>
        <button @click="disconnectError = ''" class="close-btn">×</button>
      </div>
      <div v-else-if="sharingSuccess" class="success-message">
        <p>{{ sharingSuccess }}</p>
        <button @click="sharingSuccess = ''" class="close-btn">×</button>
      </div>
      <div v-else-if="connectedUsers.length === 0" class="no-requests">
        <p>Vous n'avez pas de partenaires connectés.</p>
      </div>
      <div v-else>
        <h2>Vos partenaires</h2>
        <div class="requests-list">
          <div 
            v-for="partner in connectedUsers" 
            :key="partner.id" 
            class="request-card"
          >
            <div class="request-info">
              <p class="request-to"><strong>{{ partner.first_name }} {{ partner.last_name }}</strong></p>
            </div>
            <div class="request-actions">
              <!-- Nouveau bouton pour vérifier les doublons -->
              <button 
                @click="checkDuplicatesWithPartner(partner)"
                class="action-button check-btn"
              >
                Vérifier doublons
              </button>
              <!-- Nouveau bouton pour partager un enfant -->
              <button 
                @click="initiateKidSharing(partner)"
                class="action-button share-btn"
              >
                Lier un enfant
              </button>
              <button 
                @click="initiateDisconnect(partner)"
                class="action-button decline-btn"
              >
                Se déconnecter
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Modales -->
    <DuplicateKidsModal 
      :duplicates="potentialDuplicates"
      :isVisible="showDuplicatesModal"
      @close="showDuplicatesModal = false"
      @merged="handleMergeCompleted"
    />
    <ConfirmModal
      :isVisible="showDisconnectModal"
      :title="'Confirmer la déconnexion'"
      :message="selectedPartner ? 
        `Êtes-vous sûr de vouloir vous déconnecter de ${selectedPartner.first_name} ${selectedPartner.last_name} ?` :
        'Êtes-vous sûr de vouloir vous déconnecter de ce partenaire ?'"
      @confirm="confirmDisconnect"
      @cancel="showDisconnectModal = false"
    />
    <!-- Nouvelle modale pour le partage d'enfants -->
    <KidSharingModal
    v-if="showSharingModal"
    :isVisible="showSharingModal"
    :partner="selectedPartnerForSharing"
    :kids="userKids"
    :errorMessage="sharingError"
    @close="showSharingModal = false"
    @share="shareKidWith"
    />
  </div>
</template>

<style scoped>
/* Structure globale */
.connect-user-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

h1 {
  text-align: center;
  margin-bottom: 2rem;
  color: #333;
}

h2 {
  color: #358E9D;
  margin-bottom: 1rem;
}

/* Onglets de navigation */
.tabs {
  display: flex;
  margin-bottom: 20px;
  border-bottom: 2px solid #eee;
}

.tabs button {
  flex: 1;
  padding: 12px;
  background: none;
  border: none;
  border-bottom: 3px solid transparent;
  font-weight: bold;
  color: #666;
  cursor: pointer;
  position: relative;
  transition: all 0.3s ease;
}

.tabs button:hover {
  color: #358E9D;
}

.tabs button.active {
  color: #358E9D;
  border-bottom-color: #358E9D;
}

.badge {
  background-color: #ff4d4d;
  color: white;
  border-radius: 50%;
  padding: 2px 6px;
  font-size: 0.8rem;
  margin-left: 5px;
  font-weight: bold;
}

/* Cartes et sections */
.info-card, .connect-form-card, .success-card, .requests-section {
  background: white;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.info-card:hover, .connect-form-card:hover, .success-card:hover {
  border-color: #78BB99;
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.25);
}

/* Messages de succès et d'erreur */
.success-message, .error-message {
  position: relative;
  padding: 15px 15px 15px 20px;
  margin-bottom: 20px;
  border-radius: 6px;
}

.success-message {
  background-color: #f1f8e9;
  border-left: 4px solid #8bc34a;
  color: #33691e;
}

.error-message {
  background-color: #ffebee;
  border-left: 4px solid #f44336;
  color: #b71c1c;
}

.close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: inherit;
}

.close-btn:hover {
  opacity: 0.7;
}

/* Formulaire de connexion */
.connect-form {
  margin-top: 1rem;
}

.input-group {
  display: flex;
  gap: 10px;
}

.link-code-input {
  flex: 1;
  padding: 10px;
  font-size: 16px;
  border: 2px solid #ddd;
  border-radius: 4px;
  text-transform: uppercase;
}

.connect-button {
  background-color: #358E9D;
  color: white;
  border: none;
  padding: 0 1.5rem;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.3s;
}

.connect-button:hover:not(:disabled) {
  background-color: #2d7a87;
}

.connect-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.error-message {
  color: #f44336;
  margin-top: 0.5rem;
  font-size: 0.9rem;
}

/* Carte de succès */
.success-card {
  background-color: #f1f8e9;
  border-left: 4px solid #8bc34a;
}

.partner-info {
  background-color: #f5f5f5;
  padding: 15px;
  border-radius: 6px;
  margin: 1rem 0;
}

.status-message {
  color: #757575;
  font-style: italic;
}

/* Liste des demandes */
.requests-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.request-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  background: white;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  transition: all 0.2s ease;
}

.request-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.request-info {
  display: flex;
  flex-direction: column;
}

.request-from, .request-to {
  margin: 0;
  font-size: 1.1rem;
}

.request-actions {
  display: flex;
  gap: 10px;
}

.action-button {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  transition: background-color 0.2s;
}

.accept-btn {
  background-color: #568203;
  color: white;
}

.accept-btn:hover {
  background-color: #496e03;
}

.decline-btn {
  background-color: #E26D5C;
  color: white;
}

.decline-btn:hover {
  background-color: #d65c4a;
}

/* Nouveaux boutons */
.check-btn {
  background-color: #607D8B;
  color: white;
}

.check-btn:hover {
  background-color: #546E7A;
}

.share-btn {
  background-color: #FF9800;
  color: white;
}

.share-btn:hover {
  background-color: #F57C00;
}

.request-status {
  padding: 6px 10px;
  border-radius: 4px;
  font-size: 0.9rem;
  font-weight: bold;
}

.request-status.pending {
  background-color: #fff8e1;
  color: #F9A825;
}

.request-status.accepted {
  background-color: #e8f5e9;
  color: #2E7D32;
}

.request-status.declined {
  background-color: #ffebee;
  color: #B71C1C;
}

/* États spéciaux */
.loading, .no-requests {
  text-align: center;
  padding: 2rem;
  color: #757575;
}

.no-requests p {
  font-style: italic;
}

/* Media queries pour responsive */
@media (max-width: 768px) {
  .tabs {
    flex-direction: column;
  }
  
  .tabs button {
    border-bottom: none;
    border-left: 3px solid transparent;
    text-align: left;
    padding-left: 15px;
  }
  
  .tabs button.active {
    border-bottom: none;
    border-left-color: #358E9D;
  }
  .request-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .request-actions {
    width: 100%;
    justify-content: space-between;
    flex-wrap: wrap;
  }
  
  .action-button {
    flex: 1;
    min-width: 120px;
    margin-bottom: 8px;
    text-align: center;
  }
}

@media (max-width: 600px) {
  .input-group {
    flex-direction: column;
  }
  
  .connect-button {
    padding: 10px;
    margin-top: 10px;
  }
}
</style>