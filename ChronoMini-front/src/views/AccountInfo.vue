<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { getUser, updateUser, deleteUser } from '@/_services/UserService';
import type { User } from '@/_models/User';
import LoadingIcon from '@/components/LoadingIcon.vue';
import { toast } from 'vue3-toastify'; 
import 'vue3-toastify/dist/index.css'; 
import { useRouter } from 'vue-router'; 
import { useAuthStore } from '@/stores/auth';
import { useRecordStore } from '@/stores/record';
import { useBreakStore } from '@/stores/break';

const router = useRouter();
const user = ref<User | null>(null);
const errorMessage = ref<string | null>(null);
const fieldErrors = ref<{ [key: string]: string[] }>({});
const isLoading = ref<boolean>(true);
const editMode = ref<{ [key: string]: boolean }>({});
const isSaving = ref<{ [key: string]: boolean }>({});
const authStore = useAuthStore();
const recordStore = useRecordStore();
const breakStore = useBreakStore();
const editedValues = ref<{ [key: string]: string }>({
  first_name: '',
  last_name: '',
  email: '',
  city: '',
  zip_code: '',
  password: '',
  password_confirmation: '',
  link_code: ''
});

// États pour la modale de suppression de compte
const showDeleteModal = ref(false);
const isDeleting = ref(false);
const deleteError = ref<string | null>(null);

// Pour gérer la visibilité des mots de passe
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

type EditableFields = 'first_name' | 'last_name' | 'email' | 'city' | 'zip_code' | 'password';

// Fonction pour charger le profil utilisateur
const loadUserProfile = async () => {
  isLoading.value = true;
  try {
    const userData = await getUser();
    user.value = userData;
    // Initialiser les valeurs éditées avec les valeurs actuelles
    if (userData) {
      editedValues.value = {
        first_name: userData.first_name || '',
        last_name: userData.last_name || '',
        email: userData.email || '',
        city: userData.city || '',
        zip_code: userData.zip_code || '',
        password: '',
        password_confirmation: ''
      };
    }
  } catch (error) {
    errorMessage.value = 'Erreur lors de la récupération du profil. Veuillez réessayer plus tard.';
  } finally {
    isLoading.value = false;
  }
};

// Fonction pour basculer le mode d'édition
const toggleEdit = (field: EditableFields) => {
  editMode.value[field] = !editMode.value[field];
  if (!editMode.value[field]) {
    // Si on quitte le mode d'édition, on réinitialise les valeurs
    if (field === 'password') {
      editedValues.value.password = '';
      editedValues.value.password_confirmation = '';
    } else {
      editedValues.value[field] = user.value?.[field] as string || '';
    }
    // Réinitialiser les erreurs
    fieldErrors.value[field] = [];
  }
};

// Fonction pour basculer la visibilité du mot de passe
const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};

// Fonction pour basculer la visibilité de la confirmation du mot de passe
const togglePasswordConfirmationVisibility = () => {
  showPasswordConfirmation.value = !showPasswordConfirmation.value;
};

// Fonction pour enregistrer un champ
const saveField = async (field: EditableFields) => {
  try {
    isSaving.value[field] = true;
    // Réinitialiser les erreurs pour ce champ
    fieldErrors.value[field] = [];
    
    if (user.value) {
      const updateData: Partial<User> = {};
      
      if (field === 'password') {
        // Pour le mot de passe, nous devons inclure la confirmation
        updateData.password = editedValues.value.password;
        updateData.password_confirmation = editedValues.value.password_confirmation;
      } else {
        updateData[field] = editedValues.value[field];
      }
      
      await updateUser(updateData);
      
      // Mettre à jour les données utilisateur locales
      if (field !== 'password') {
        (user.value[field] as string) = editedValues.value[field];
      }
      
      // Réinitialiser les champs de mot de passe
      if (field === 'password') {
        editedValues.value.password = '';
        editedValues.value.password_confirmation = '';
      }
      
      // Quitter le mode édition
      editMode.value[field] = false;
    }
  } catch (error: any) {
    // Gérer les erreurs de validation de l'API
    if (error.response && error.response.data && error.response.data.errors) {
      // Récupérer les erreurs spécifiques au champ
      fieldErrors.value[field] = error.response.data.errors[field] || [];
      
      // S'il y a des erreurs générales
      if (error.response.data.errors.general) {
        errorMessage.value = error.response.data.errors.general.join(', ');
      }
    } else {
      // Erreur générale
      errorMessage.value = `Erreur lors de la sauvegarde: ${error.message || 'Erreur inconnue'}`;
    }
  } finally {
    isSaving.value[field] = false;
  }
};

// Ouvrir la modale de suppression de compte
const openDeleteModal = () => {
  showDeleteModal.value = true;
  deleteError.value = null;
};

// Fermer la modale de suppression de compte
const closeDeleteModal = () => {
  showDeleteModal.value = false;
  deleteError.value = null;
};

// Fonction pour supprimer le compte
const confirmDeleteAccount = async () => {
  try {
    isDeleting.value = true;
    deleteError.value = null;
    
    await deleteUser();
    
    if (recordStore.activeRecord && 'value' in recordStore.activeRecord) {
      recordStore.activeRecord.value = null;
    } else {
      recordStore.activeRecord = null;
    }

    if (breakStore.activeBreaks && 'value' in breakStore.activeBreaks) {
      breakStore.activeBreaks.value = {};
    } else {
      breakStore.activeBreaks = {};
    }
    
    await authStore.logout();

    toast.success('Votre compte a été supprimé avec succès.', {
      position: toast.POSITION.TOP_RIGHT,
      autoClose: 3000,
      progressStyle: { backgroundColor: "#568203" },
      toastStyle: { "--toastify-icon-color-success": "#568203" },
    });

    setTimeout(() => {
      router.push('/register-login');
    }, 2000);
    
  } catch (error: any) {
    deleteError.value = error.response?.data?.message || 'Une erreur est survenue lors de la suppression du compte.';
  } finally {
    isDeleting.value = false;
  }
};

const copyToClipboard = (text: string) => {
  navigator.clipboard.writeText(text)
    .then(() => {
      toast.success(`Code Copié !`, {
            position: toast.POSITION.TOP_RIGHT,
            autoClose: 1500,
            progressStyle: { backgroundColor: "#568203" },
            toastStyle: {
            "--toastify-icon-color-success": "#568203", // Change la couleur de la coche
            },
        });
    })
    .catch(err => {
        toast.error('Impossible de copier le code. Veuillez réessayer.', {
        position: toast.POSITION.TOP_RIGHT,
      autoClose: 3000
    });
  });
};

onMounted(() => {
  loadUserProfile();
});
</script>

<template>
  <div class="profile-container">
    <h1>Mon Profil</h1>
    <LoadingIcon v-if="isLoading" />
    
    <div v-if="user" class="profile-card">
      <h2>Informations utilisateur</h2>
      <!-- Champ Nom -->
      <div class="field-container">
        <label>Nom :</label>
        <div class="field-value">
          <template v-if="!editMode.last_name">
            {{ user.last_name }}
            <font-awesome-icon 
              v-if="isSaving.last_name"
              icon="fa-solid fa-spinner"
              class="spinner-icon" 
              spin
            />
            <font-awesome-icon 
              v-else
              icon="fa-solid fa-pen"  
              class="edit-icon" 
              @click="toggleEdit('last_name')" 
            />
          </template>
          <div v-else class="edit-inputs">
            <div class="input-wrapper">
              <input v-model="editedValues.last_name" placeholder="Nom" />
              <div v-if="fieldErrors.last_name && fieldErrors.last_name.length" class="field-error">
                <p v-for="error in fieldErrors.last_name" :key="error">{{ error }}</p>
              </div>
            </div>
            <div class="edit-actions">
              <font-awesome-icon 
                v-if="isSaving.last_name"
                icon="fa-solid fa-spinner"
                class="spinner-icon" 
                spin
              />
              <font-awesome-icon 
                v-else
                icon="fa-solid fa-check"
                class="save-icon" 
                @click="saveField('last_name')" 
              />
              <font-awesome-icon 
                icon="fa-solid fa-ban"
                class="cancel-icon" 
                @click="toggleEdit('last_name')" 
              />
            </div>
          </div>
        </div>
      </div>
      <!-- Champ Prénom -->
      <div class="field-container">
        <label>Prénom :</label>
        <div class="field-value">
          <template v-if="!editMode.first_name">
            {{ user.first_name }}
            <font-awesome-icon 
              v-if="isSaving.first_name"
              icon="fa-solid fa-spinner"
              class="spinner-icon" 
              spin
            />
            <font-awesome-icon 
              v-else
              icon="fa-solid fa-pen"  
              class="edit-icon" 
              @click="toggleEdit('first_name')" 
            />
          </template>
          <div v-else class="edit-inputs">
            <div class="input-wrapper">
              <input v-model="editedValues.first_name" placeholder="Prénom" />
              <div v-if="fieldErrors.first_name && fieldErrors.first_name.length" class="field-error">
                <p v-for="error in fieldErrors.first_name" :key="error">{{ error }}</p>
              </div>
            </div>
            <div class="edit-actions">
              <font-awesome-icon 
                v-if="isSaving.first_name"
                icon="fa-solid fa-spinner"
                class="spinner-icon" 
                spin
              />
              <font-awesome-icon 
                v-else
                icon="fa-solid fa-check"
                class="save-icon" 
                @click="saveField('first_name')" 
              />
              <font-awesome-icon 
                icon="fa-solid fa-ban"
                class="cancel-icon" 
                @click="toggleEdit('first_name')" 
              />
            </div>
          </div>
        </div>
      </div>
      <!-- Champ Email -->
      <div class="field-container">
        <label>Email :</label>
        <div class="field-value">
          <template v-if="!editMode.email">
            {{ user.email }}
            <font-awesome-icon 
              v-if="isSaving.email"
              icon="fa-solid fa-spinner"
              class="spinner-icon" 
              spin
            />
            <font-awesome-icon 
              v-else
              icon="fa-solid fa-pen"
              class="edit-icon" 
              @click="toggleEdit('email')" 
            />
          </template>
          <div v-else class="edit-inputs">
            <div class="input-wrapper">
              <input v-model="editedValues.email" type="email" placeholder="Email" />
              <div v-if="fieldErrors.email && fieldErrors.email.length" class="field-error">
                <p v-for="error in fieldErrors.email" :key="error">{{ error }}</p>
              </div>
            </div>
            <div class="edit-actions">
              <font-awesome-icon 
                v-if="isSaving.email"
                icon="fa-solid fa-spinner"
                class="spinner-icon" 
                spin
              />
              <font-awesome-icon 
                v-else
                icon="fa-solid fa-check" 
                class="save-icon" 
                @click="saveField('email')" 
              />
              <font-awesome-icon 
                icon="fa-solid fa-ban" 
                class="cancel-icon" 
                @click="toggleEdit('email')" 
              />
            </div>
          </div>
        </div>
      </div>
      
      <!-- Champ Ville -->
      <div class="field-container">
        <label>Ville :</label>
        <div class="field-value">
          <template v-if="!editMode.city">
            {{ user.city || 'Non renseigné' }}
            <font-awesome-icon 
              v-if="isSaving.city"
              icon="fa-solid fa-spinner"
              class="spinner-icon" 
              spin
            />
            <font-awesome-icon 
              v-else
              icon="fa-solid fa-pen" 
              class="edit-icon" 
              @click="toggleEdit('city')" 
            />
          </template>
          <div v-else class="edit-inputs">
            <div class="input-wrapper">
              <input v-model="editedValues.city" placeholder="Ville" />
              <div v-if="fieldErrors.city && fieldErrors.city.length" class="field-error">
                <p v-for="error in fieldErrors.city" :key="error">{{ error }}</p>
              </div>
            </div>
            <div class="edit-actions">
              <font-awesome-icon 
                v-if="isSaving.city"
                icon="fa-solid fa-spinner"
                class="spinner-icon" 
                spin
              />
              <font-awesome-icon 
                v-else
                icon="fa-solid fa-check" 
                class="save-icon" 
                @click="saveField('city')" 
              />
              <font-awesome-icon 
                icon="fa-solid fa-ban" 
                class="cancel-icon" 
                @click="toggleEdit('city')" 
              />
            </div>
          </div>
        </div>
      </div>
      <!-- Champ Code Postal -->
      <div class="field-container">
        <label>Code Postal :</label>
        <div class="field-value">
          <template v-if="!editMode.zip_code">
            {{ user.zip_code || 'Non renseigné' }}
            <font-awesome-icon 
              v-if="isSaving.zip_code"
              icon="fa-solid fa-spinner"
              class="spinner-icon" 
              spin
            />
            <font-awesome-icon 
              v-else
              icon="fa-solid fa-pen" 
              class="edit-icon" 
              @click="toggleEdit('zip_code')" 
            />
          </template>
          <div v-else class="edit-inputs">
            <div class="input-wrapper">
              <input v-model="editedValues.zip_code" placeholder="Code postal" />

              <div v-if="fieldErrors.zip_code && fieldErrors.zip_code.length" class="field-error">
                <p v-for="error in fieldErrors.zip_code" :key="error">{{ error }}</p>
              </div>
            </div>
            <div class="edit-actions">
              <font-awesome-icon 
                v-if="isSaving.zip_code"
                icon="fa-solid fa-spinner"
                class="spinner-icon" 
                spin
              />
              <font-awesome-icon 
                v-else
                icon="fa-solid fa-check" 
                class="save-icon" 
                @click="saveField('zip_code')" 
              />
              <font-awesome-icon 
                icon="fa-solid fa-ban" 
                class="cancel-icon" 
                @click="toggleEdit('zip_code')" 
              />
            </div>
          </div>
        </div>
      </div>
      
      <!-- Champ Mot de Passe -->
      <div class="field-container">
        <label>Mot de passe :</label>
        <div class="field-value">
          <template v-if="!editMode.password">
            ••••••••
            <font-awesome-icon 
              v-if="isSaving.password"
              icon="fa-solid fa-spinner"
              class="spinner-icon" 
              spin
            />
            <font-awesome-icon 
              v-else
              icon="fa-solid fa-pen" 
              class="edit-icon" 
              @click="toggleEdit('password')" 
            />
          </template>
          <div v-else class="edit-inputs password-edit">
            <div class="input-wrapper">
              <!-- Mot de passe -->
              <div class="password-field">
                <div class="password-wrapper">
                  <input 
                    :type="showPassword ? 'text' : 'password'" 
                    v-model="editedValues.password" 
                    placeholder="Nouveau mot de passe" 
                  />
                  <button type="button" class="toggle-password" @click="togglePasswordVisibility">
                    <span v-if="showPassword">
                      <font-awesome-icon icon="fa-solid fa-eye-slash" />
                    </span>
                    <span v-else>
                      <font-awesome-icon icon="fa-solid fa-eye" />
                    </span>
                  </button>
                </div>
              </div>
              <!-- Confirmation mot de passe -->
              <div class="password-field">
                <div class="password-wrapper">
                  <input 
                    :type="showPasswordConfirmation ? 'text' : 'password'" 
                    v-model="editedValues.password_confirmation" 
                    placeholder="Confirmation mot de passe" 
                  />
                  <button type="button" class="toggle-password" @click="togglePasswordConfirmationVisibility">
                    <span v-if="showPasswordConfirmation">
                      <font-awesome-icon icon="fa-solid fa-eye-slash" />
                    </span>
                    <span v-else>
                      <font-awesome-icon icon="fa-solid fa-eye" />
                    </span>
                  </button>
                </div>
              </div>
              <div v-if="fieldErrors.password && fieldErrors.password.length" class="field-error">
                <p v-for="error in fieldErrors.password" :key="error">{{ error }}</p>
              </div>
            </div>
            <div class="edit-actions">
              <font-awesome-icon 
                v-if="isSaving.password"
                icon="fa-solid fa-spinner"
                class="spinner-icon" 
                spin
              />
              <font-awesome-icon 
                v-else
                icon="fa-solid fa-check" 
                class="save-icon" 
                @click="saveField('password')" 
              />
              <font-awesome-icon 
                icon="fa-solid fa-ban" 
                class="cancel-icon" 
                @click="toggleEdit('password')" 
              />
            </div>
          </div>
        </div>
      </div>
      
      <!-- Champ Code de liaison -->
      <div class="field-container">
        <label>Code de liaison : </label>
        <div class="field-value">
          {{ user.link_code }}
          <button 
            @click="user.link_code && copyToClipboard(user.link_code)" 
            class="copy-button"
            :disabled="!user.link_code"
          >
            <font-awesome-icon icon="fa-solid fa-copy" />
          </button>
          <div class="link-code-help">
            Partagez ce code avec d'autres utilisateurs pour vous connecter avec eux.
          </div>
        </div>
      </div>
    </div>
    
    <!-- Section de suppression de compte -->
    <div class="delete-account-section">
      <button @click="openDeleteModal" class="delete-account-btn">
        <font-awesome-icon icon="fa-solid fa-trash" />
        Supprimer mon compte
      </button>
    </div>
    
    <!-- Modale de confirmation de suppression de compte -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDeleteModal">
      <div class="modal-content">
        <h3>Supprimer mon compte</h3>
        <div class="warning-icon">
          <font-awesome-icon icon="fa-solid fa-triangle-exclamation" />
        </div>
        <p class="modal-text">Êtes-vous sûr de vouloir supprimer votre compte ?</p>
        <p class="modal-warning">Cette action est <strong>irréversible</strong> et entraînera la suppression définitive de toutes vos informations personnelles et des données associées à votre compte.</p>
        
        <p class="modal-details">En confirmant, vous comprenez que :</p>
        <ul class="modal-list">
          <li>Toutes vos informations personnelles seront définitivement supprimées</li>
          <li>Si vous êtes le seul utilisateur lié à des enfants, leurs données et historiques seront également supprimés</li>
          <li>Si d'autres utilisateurs sont liés à ces enfants, ils conserveront l'accès à ces données</li>
          <li>Vous devrez créer un nouveau compte si vous souhaitez utiliser notre service à nouveau</li>
        </ul>
        
        <div v-if="deleteError" class="delete-error">
          {{ deleteError }}
        </div>
        
        <div class="modal-actions">
          <button @click="closeDeleteModal" class="cancel-button">
            Annuler
          </button>
          <button 
            @click="confirmDeleteAccount" 
            class="confirm-button"
            :disabled="isDeleting"
          >
            <font-awesome-icon v-if="isDeleting" icon="fa-solid fa-spinner" spin />
            <span v-else>Je confirme la suppression</span>
          </button>
        </div>
      </div>
    </div>
    
    <div v-if="errorMessage" class="error">
      <p>{{ errorMessage }}</p>
    </div>
  </div>
</template>

<style scoped>
.profile-container {
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
  text-align: center;
}

.profile-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.field-container {
  display: flex;
  align-items: flex-start;
  margin-bottom: 15px;
  padding: 10px;
  border-bottom: 1px solid #eee;
}

.field-container label {
  width: 150px;
  font-weight: bold;
  color: #666;
}

.field-value {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 10px;
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
  align-items: flex-start;
  flex: 1;
}

.password-edit {
  flex-direction: column;
}

.edit-inputs input {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  flex: 1;
}

.edit-actions {
  display: flex;
  gap: 5px;
  align-self: center;
}

.save-icon {
  cursor: pointer;
  color: #4CAF50;
}

.cancel-icon {
  cursor: pointer;
  color: #f44336;
}

.spinner-icon {
  color: #2196F3;
}

.field-error {
  color: #f44336;
  font-size: 12px;
  margin-top: 5px;
  text-align: left;
}

.input-wrapper {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.error {
  color: #f44336;
  margin-top: 20px;
  padding: 10px;
  border-radius: 4px;
  background-color: #ffebee;
}

.password-wrapper {
  display: flex;
  align-items: center;
  position: relative;
  width: 100%;
  margin-bottom: 10px;
}

.toggle-password {
  background: none;
  border: none;
  font-size: 1.2em;
  cursor: pointer;
  color: #666;
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
}

.password-field {
  width: 100%;
  margin-bottom: 10px;
}

.link-code {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: bold;
  color: #358E9D;
  font-size: 1rem;
}

.copy-button {
  background: none;
  border: none;
  cursor: pointer;
  color: #666;
  opacity: 0.6;
  transition: opacity 0.2s;
  font-size: 1rem;
  padding: 5px;
}

.copy-button:hover {
  opacity: 1;
  color: #358E9D;
}

.link-code-help {
  font-size: 0.8rem;
  color: #666;
  margin-top: 5px;
  font-style: italic;
}

/* Styles pour la section de suppression de compte */
.delete-account-section {
  margin-top: 30px;
  padding: 20px;
  border-top: 1px solid #eee;
}

.delete-account-btn {
  background-color: #f44336;
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0 auto;
  transition: background-color 0.3s;
}

.delete-account-btn:hover {
  background-color: #d32f2f;
}

/* Styles pour la modale de suppression */
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
  border-radius: 8px;
  padding: 30px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
  text-align: center;
}

.warning-icon {
  font-size: 3rem;
  color: #f44336;
  margin: 20px 0;
}

.modal-text {
  font-size: 1.2rem;
  margin-bottom: 15px;
}

.modal-warning {
  color: #f44336;
  font-weight: 500;
  margin-bottom: 20px;
}

.modal-details {
  text-align: left;
  font-weight: bold;
  margin-bottom: 5px;
}

.modal-list {
  text-align: left;
  margin: 0 0 20px 20px;
  color: #666;
}

.modal-list li {
  margin-bottom: 8px;
}

.modal-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 30px;
}

.cancel-button {
  background-color: #f0f0f0;
  color: #333;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.cancel-button:hover {
  background-color: #e0e0e0;
}

.confirm-button {
  background-color: #f44336;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.confirm-button:hover {
  background-color: #d32f2f;
}

.confirm-button:disabled {
  background-color: #ffccbc;
  cursor: not-allowed;
}

.delete-error {
  color: #f44336;
  margin: 15px 0;
  padding: 10px;
  background-color: #ffebee;
  border-radius: 4px;
  text-align: center;
}

@media (max-width: 768px) {
  .field-container {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .field-container label {
    width: 100%;
    margin-bottom: 5px;
  }
  
  .field-value {
    width: 100%;
  }
  
  .edit-inputs {
    flex-direction: column;
    width: 100%;
  }
  
  .edit-actions {
    flex-direction: row;
    justify-content: flex-end;
    margin-top: 10px;
  }
  
  .modal-actions {
    flex-direction: column;
    gap: 10px;
  }
  
  .cancel-button, .confirm-button {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .profile-card {
    padding: 15px 10px;
  }
  
  .modal-content {
    padding: 20px 15px;
  }
  
  .warning-icon {
    font-size: 2.5rem;
    margin: 15px 0;
  }
  
  .modal-text {
    font-size: 1.1rem;
  }
}
</style>