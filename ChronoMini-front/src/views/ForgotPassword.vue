<script setup lang="ts">
import { ref } from 'vue';
import * as PasswordResetService from '@/_services/PasswordResetService';

const email = ref('');
const isSubmitting = ref(false);
const status = ref<'idle' | 'success' | 'error'>('idle');
const errorMessage = ref('');
const validationErrors = ref<Record<string, string[]>>({});

async function handleSubmit() {
  if (!email.value) return;
  
  isSubmitting.value = true;
  status.value = 'idle';
  validationErrors.value = {}; // Réinitialiser les erreurs
  
  try {
    await PasswordResetService.requestPasswordReset(email.value);
    status.value = 'success';
  } catch (error: any) {
    status.value = 'error';
    
    if (error.validationErrors) {
      // Stocker les erreurs de validation
      validationErrors.value = error.validationErrors;
      errorMessage.value = error.message || 'Des erreurs de validation sont survenues.';
    } else {
      errorMessage.value = 'Une erreur est survenue. Veuillez réessayer.';
    }
  } finally {
    isSubmitting.value = false;
  }
}
</script>

<template>
  <main>
    <div class="password-reset-container">
      <h1>Réinitialisation de mot de passe</h1>
      
      <div v-if="status === 'success'" class="success-message">
        <div class="success-icon">✓</div>
        <p>Un email contenant les instructions de réinitialisation a été envoyé à votre adresse email.</p>
      </div>
      
      <form v-else @submit.prevent="handleSubmit">
        <div class="form-group">
          <label for="email">Adresse email</label>
          <input 
            type="email" 
            id="email" 
            v-model="email" 
            required 
            placeholder="Entrez votre adresse email"
            :class="{'has-error': validationErrors.email}"
          />
          <!-- Affichage des erreurs spécifiques au champ email -->
          <div v-if="validationErrors.email" class="field-error">
            {{ validationErrors.email[0] }}
          </div>
        </div>
        
        <div v-if="errorMessage && !validationErrors.email" class="error-message">
          {{ errorMessage }}
        </div>
        
        <button 
          type="submit" 
          class="submit-button" 
          :disabled="isSubmitting"
        >
          <span v-if="isSubmitting">Envoi en cours...</span>
          <span v-else>Envoyer les instructions</span>
        </button>
      </form>
      
      <div class="login-link">
        <router-link to="/register-login">Retour à la page de connexion</router-link>
      </div>
    </div>
  </main>
</template>

<style scoped>

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

.password-reset-container {
  background-color: white;
  border-radius: 20px;
  padding: 40px;
  max-width: 500px;
  width: 90%;
  text-align: center;
  box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.1);
}

h1 {
  color: #78BB99;
  margin-bottom: 30px;
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

.submit-button {
  background-color: #358E9D;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 15px 25px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s;
  width: 100%;
  margin-top: 10px;
}

.submit-button:hover {
  background-color: #286C72;
}

.submit-button:disabled {
  background-color: #aaa;
  cursor: not-allowed;
}

.error-message {
  background-color: #ffecec;
  color: #e74c3c;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-weight: bold;
}

.success-message {
  background-color: #e8f9f2;
  color: #78BB99;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.success-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 0 auto 20px;
  font-size: 30px;
  font-weight: bold;
  background-color: #78BB99;
  color: white;
}

.login-link {
  margin-top: 25px;
}

.login-link a {
  color: #358E9D;
  text-decoration: none;
}

.login-link a:hover {
  text-decoration: underline;
}

.field-error {
  color: #e74c3c;
  font-size: 14px;
  margin-top: 5px;
  text-align: left;
}

.has-error {
  border-color: #e74c3c !important;
}
</style>