<script setup lang="ts">
import { onMounted, ref, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import * as PasswordResetService from '@/_services/PasswordResetService';

const route = useRoute();
const router = useRouter();

const email = ref<string>('');
const token = ref<string>('');
const password = ref<string>('');
const passwordConfirmation = ref<string>('');

const isLoading = ref(true);
const isSubmitting = ref(false);
const status = ref<'loading' | 'valid' | 'invalid' | 'success' | 'error'>('loading');
const errorMessage = ref<string>('');
const redirectCountdown = ref(10);
let countdownInterval: number | null = null;

// Variables pour la visibilité des mots de passe
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// Vérification de la concordance des mots de passe
const passwordsMatch = computed(() => {
  if (password.value === '' || passwordConfirmation.value === '') return null;
  return password.value === passwordConfirmation.value;
});

function clearCountdown() {
    if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = null;
    }
}

// Fonction pour rediriger et arrêter le compte à rebours
function redirectToLogin() {
    clearCountdown(); // Arrêter le compte à rebours quand on clique sur le bouton
    router.push('/register-login');
}

// Réinitialiser le message d'erreur quand les mots de passe changent
watch([password, passwordConfirmation], () => {
  if (errorMessage.value === 'Les mots de passe ne correspondent pas.') {
    errorMessage.value = '';
  }
});

// Vérifier le token lors du chargement du composant
onMounted(async () => {
  // Récupérer les paramètres de l'URL
  email.value = route.query.email as string;
  token.value = route.query.token as string;
  
  if (!email.value || !token.value) {
    status.value = 'invalid';
    errorMessage.value = 'Paramètres manquants dans l\'URL.';
    isLoading.value = false;
    return;
  }
  
  try {
    // Vérifier si le token est valide
    await PasswordResetService.verifyResetToken(email.value, token.value);
    status.value = 'valid';
  } catch (error: any) {
    status.value = 'invalid';
    errorMessage.value = error.response?.data?.error || 'Token invalide ou expiré.';
  } finally {
    isLoading.value = false;
  }
});

// Réinitialiser le mot de passe
async function handleSubmit() {
  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = 'Les mots de passe ne correspondent pas.';
    return;
  }
  
  isSubmitting.value = true;
  
  try {
    await PasswordResetService.resetPassword(
      email.value,
      token.value,
      password.value,
      passwordConfirmation.value
    );
    
    status.value = 'success';
    
    // Démarrer le compte à rebours pour la redirection
    countdownInterval = setInterval(() => {
      redirectCountdown.value--;
      if (redirectCountdown.value <= 0) {
        clearInterval(countdownInterval as number);
        router.push('/register-login');
      }
    }, 1000) as unknown as number;
    
  } catch (error: any) {
    status.value = 'error';
    errorMessage.value = error.response?.data?.error || 'Une erreur est survenue. Veuillez réessayer.';
  } finally {
    isSubmitting.value = false;
  }
}

// Fonctions pour basculer la visibilité des mots de passe
function togglePasswordVisibility() {
  showPassword.value = !showPassword.value;
}

function toggleConfirmationVisibility() {
  showPasswordConfirmation.value = !showPasswordConfirmation.value;
}

// Nettoyage au démontage du composant
onMounted(() => {
  return () => {
    if (countdownInterval) {
      clearInterval(countdownInterval);
    }
  };
});
</script>

<template>
  <main>
    <div class="reset-password-container">
      <!-- État de chargement -->
      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <h2>Vérification du lien...</h2>
        <p>Nous vérifions votre demande de réinitialisation.</p>
      </div>
      
      <!-- Lien invalide ou expiré -->
      <div v-else-if="status === 'invalid'" class="error-state">
        <div class="error-icon">!</div>
        <h1>Lien invalide</h1>
        <div class="alert-message">{{ errorMessage }}</div>
        <p>Le lien de réinitialisation est invalide ou a expiré.</p>
        <button class="retry-button" @click="router.push('/forgot-password')">
          Demander un nouveau lien
        </button>
      </div>
      
      <!-- Formulaire de réinitialisation -->
      <div v-else-if="status === 'valid'" class="form-state">
        <h1>Définir un nouveau mot de passe</h1>
        <p>Veuillez choisir un nouveau mot de passe pour votre compte.</p>
        
        <form @submit.prevent="handleSubmit">
          <div class="form-group">
            <label for="password">Nouveau mot de passe</label>
            <div class="password-wrapper">
              <input 
                :type="showPassword ? 'text' : 'password'" 
                id="password" 
                v-model="password" 
                required 
                minlength="8"
                placeholder="Minimum 8 caractères"
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
          
          <div class="form-group">
            <label for="password-confirmation">Confirmer le mot de passe</label>
            <div class="password-wrapper">
              <input 
                :type="showPasswordConfirmation ? 'text' : 'password'" 
                id="password-confirmation" 
                v-model="passwordConfirmation" 
                required
                placeholder="Confirmer votre mot de passe"
                :class="{'match': passwordsMatch === true, 'mismatch': passwordsMatch === false}"
              />
              <button type="button" class="toggle-password" @click="toggleConfirmationVisibility">
                <span v-if="showPasswordConfirmation">
                  <font-awesome-icon icon="fa-solid fa-eye-slash" />
                </span>
                <span v-else>
                  <font-awesome-icon icon="fa-solid fa-eye" />
                </span>
              </button>
            </div>
            
            <!-- Indicateur de concordance des mots de passe -->
            <div v-if="passwordConfirmation.length > 0" class="password-match-indicator">
              <span v-if="passwordsMatch" class="match-indicator success">
                <font-awesome-icon icon="fa-solid fa-check" /> Les mots de passe correspondent
              </span>
              <span v-else class="match-indicator error">
                <font-awesome-icon icon="fa-solid fa-times" /> Les mots de passe ne correspondent pas
              </span>
            </div>
          </div>
          
          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>
          
          <button 
            type="submit" 
            class="submit-button" 
            :disabled="isSubmitting || (passwordConfirmation.length > 0 && !passwordsMatch)"
          >
            <span v-if="isSubmitting">Traitement en cours...</span>
            <span v-else>Réinitialiser le mot de passe</span>
          </button>
        </form>
      </div>
      
      <!-- État de succès -->
      <div v-else-if="status === 'success'" class="success-state">
        <div class="success-icon">✓</div>
        <h1>Mot de passe réinitialisé !</h1>
        <p>Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.</p>
        <p class="redirect-message">Vous serez redirigé vers la page de connexion dans <span class="countdown">{{ redirectCountdown }}</span> secondes.</p>
        <button class="login-button" @click="redirectToLogin">
          Se connecter maintenant
        </button>
      </div>
      
      <!-- État d'erreur -->
      <div v-else-if="status === 'error'" class="error-state">
        <div class="error-icon">!</div>
        <h1>Oups !</h1>
        <div class="alert-message">{{ errorMessage }}</div>
        <p>Une erreur s'est produite lors de la réinitialisation de votre mot de passe.</p>
        <button class="retry-button" @click="status = 'valid'; errorMessage = ''">
          Réessayer
        </button>
      </div>
    </div>
  </main>
</template>

<style scoped>
/* Styles existants */
main {
  background-position: top;
  background-size: cover;
  background-repeat: no-repeat;
  min-height: 85vh;
  height: auto;
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: Georgia, 'Times New Roman', Times, serif;
}

.reset-password-container {
  background-color: white;
  border-radius: 20px;
  padding: 40px;
  max-width: 600px;
  width: 90%;
  text-align: center;
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.1);
}

h1 {
  color: #78BB99;
  margin-bottom: 20px;
  font-size: 2.2rem;
}

h2 {
  color: #358E9D;
  margin-bottom: 15px;
}

p {
  font-size: 18px;
  color: #646464;
  line-height: 1.6;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
  text-align: left;
}

label {
  display: block;
  font-size: 16px;
  color: #646464;
  margin-bottom: 8px;
}

input {
  width: 100%;
  padding: 12px;
  font-size: 16px;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-sizing: border-box;
}

/* Nouvelles styles pour les mots de passe */
.password-wrapper {
  position: relative;
  width: 100%;
}

.toggle-password {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: #666;
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.password-match-indicator {
  font-size: 14px;
  margin-top: 8px;
}

.match-indicator {
  display: flex;
  align-items: center;
  gap: 5px;
}

.match-indicator.success {
  color: #78BB99;
}

.match-indicator.error {
  color: #e74c3c;
}

input.match {
  border-color: #78BB99;
}

input.mismatch {
  border-color: #e74c3c;
}

/* Styles existants */
.loading-state .spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  width: 60px;
  height: 60px;
  border-radius: 50%;
  border-left-color: #358E9D;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

.success-icon, .error-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 0 auto 20px;
  font-size: 40px;
  font-weight: bold;
}

.success-icon {
  background-color: #78BB99;
  color: white;
}

.error-icon {
  background-color: #e74c3c;
  color: white;
}

.alert-message {
  background-color: #ffecec;
  color: #e74c3c;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-weight: bold;
}

.error-message {
  background-color: #ffecec;
  color: #e74c3c;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.login-button, .retry-button, .submit-button {
  background-color: #358E9D;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 15px 25px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s;
  margin-top: 10px;
}

.submit-button {
  width: 100%;
}

.login-button:hover, .retry-button:hover, .submit-button:hover {
  background-color: #286C72;
}

.submit-button:disabled {
  background-color: #aaa;
  cursor: not-allowed;
}

.redirect-message {
  font-size: 16px;
  color: #888;
  margin-top: 20px;
}

.countdown {
  font-weight: bold;
  color: #358E9D;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .reset-password-container {
    padding: 30px 20px;
  }

  h1 {
    font-size: 1.8rem;
  }

  p {
    font-size: 16px;
  }
}
</style>