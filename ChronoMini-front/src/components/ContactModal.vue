<script setup lang="ts">
import { ref } from 'vue';
import { sendContactMessage } from '@/_services/ContactService';
import type { ContactForm } from '@/_models/ContactForm';

// Props pour contrôler l'affichage de la modal
const props = defineProps<{
  show: boolean
}>();

// Événements émis par le composant
const emit = defineEmits(['close']);

// État du formulaire
const formData = ref<ContactForm>({
  name: '',
  email: '',
  subject: '',
  message: ''
});

// État de soumission et erreurs
const formErrors = ref<Record<string, string[]>>({});
const isSubmitting = ref(false);
const submitSuccess = ref(false);
const submitError = ref('');

// Réinitialiser le formulaire
const resetForm = () => {
  formData.value = {
    name: '',
    email: '',
    subject: '',
    message: ''
  };
  formErrors.value = {};
  submitError.value = '';
  submitSuccess.value = false;
};

// Fermer la modal
const closeModal = () => {
  resetForm();
  emit('close');
};

// Soumettre le formulaire
const submitForm = async () => {
  formErrors.value = {};
  submitError.value = '';
  isSubmitting.value = true;
  
  try {
    await sendContactMessage(formData.value);
    submitSuccess.value = true;
  } catch (error: any) {
    if (error.response && error.response.data && error.response.data.errors) {
      formErrors.value = error.response.data.errors;
    } else {
      submitError.value = 'Une erreur est survenue. Veuillez réessayer plus tard.';
    }
  } finally {
    isSubmitting.value = false;
  }
};

// Préparer un nouveau message après un succès
const newMessage = () => {
  resetForm();
};
</script>

<template>
  <div v-if="show" class="modal-overlay" @click="closeModal">
    <div class="contact-modal" @click.stop>
      <button class="close-btn" @click="closeModal">
        <font-awesome-icon icon="fa-solid fa-times" />
      </button>
      
      <h2>Contactez-nous</h2>
      
      <div v-if="submitSuccess" class="success-message">
        <font-awesome-icon icon="fa-solid fa-check-circle" size="2x" />
        <p>Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.</p>
        <button @click="newMessage" class="new-message-btn">Envoyer un nouveau message</button>
      </div>
      
      <form v-else @submit.prevent="submitForm">
        <div class="form-group">
          <label for="name">Nom complet</label>
          <input 
            type="text" 
            id="name" 
            v-model="formData.name" 
            :class="{ 'error': formErrors.name }"
            placeholder="Votre nom complet"
          />
          <p v-if="formErrors.name" class="error-text">{{ formErrors.name[0] }}</p>
        </div>
        
        <div class="form-group">
          <label for="email">Email</label>
          <input 
            type="email" 
            id="email" 
            v-model="formData.email" 
            :class="{ 'error': formErrors.email }"
            placeholder="Votre adresse email"
          />
          <p v-if="formErrors.email" class="error-text">{{ formErrors.email[0] }}</p>
        </div>
        
        <div class="form-group">
          <label for="subject">Sujet</label>
          <input 
            type="text" 
            id="subject" 
            v-model="formData.subject" 
            :class="{ 'error': formErrors.subject }"
            placeholder="Sujet de votre message"
          />
          <p v-if="formErrors.subject" class="error-text">{{ formErrors.subject[0] }}</p>
        </div>
        
        <div class="form-group">
          <label for="message">Message</label>
          <textarea 
            id="message" 
            v-model="formData.message" 
            :class="{ 'error': formErrors.message }"
            placeholder="Votre message"
            rows="6"
          ></textarea>
          <p v-if="formErrors.message" class="error-text">{{ formErrors.message[0] }}</p>
        </div>
        
        <p v-if="submitError" class="global-error">{{ submitError }}</p>
        
        <div class="button-container">
          <button type="submit" :disabled="isSubmitting" class="submit-btn">
            <span v-if="isSubmitting">
              <font-awesome-icon icon="fa-solid fa-spinner" spin /> Envoi en cours...
            </span>
            <span v-else>Envoyer le message</span>
          </button>
        </div>
      </form>
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
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.contact-modal {
  background: white;
  width: 90%;
  max-width: 500px;
  border-radius: 10px;
  padding: 2rem;
  position: relative;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  max-height: 90vh;
  overflow-y: auto;
}

.close-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  background: transparent;
  border: none;
  font-size: 1.2rem;
  color: #666;
  cursor: pointer;
  transition: color 0.2s;
}

.close-btn:hover {
  color: #E26D5C;
}

h2 {
  color: #358E9D;
  margin-bottom: 1.5rem;
  text-align: center;
  font-size: 1.8rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

input, textarea {
  width: 100%;
  padding: 0.8rem 1rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
  font-family: inherit;
  box-sizing: border-box;
}

input:focus, textarea:focus {
  outline: none;
  border-color: #358E9D;
  box-shadow: 0 0 0 2px rgba(53, 142, 157, 0.1);
}

input.error, textarea.error {
  border-color: #E26D5C;
}

.error-text {
  color: #E26D5C;
  font-size: 0.875rem;
  margin-top: 0.5rem;
  margin-bottom: 0;
}

.global-error {
  background-color: rgba(226, 109, 92, 0.1);
  color: #E26D5C;
  padding: 0.8rem;
  border-radius: 6px;
  margin-bottom: 1.5rem;
  text-align: center;
}

.button-container {
  text-align: center;
}

.submit-btn {
  background-color: #358E9D;
  color: white;
  border: none;
  padding: 0.8rem 2rem;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.submit-btn:hover {
  background-color: #2a7a8a;
}

.submit-btn:disabled {
  background-color: #94c5cd;
  cursor: not-allowed;
}

.success-message {
  display: flex;
  flex-direction: column;
  align-items: center;
  background-color: rgba(120, 187, 153, 0.1);
  color: #333;
  padding: 2rem;
  border-radius: 6px;
  text-align: center;
}

.success-message svg {
  color: #78BB99;
  margin-bottom: 1rem;
}

.new-message-btn {
  background-color: #78BB99;
  color: white;
  border: none;
  padding: 0.8rem 1.5rem;
  border-radius: 6px;
  font-size: 0.875rem;
  cursor: pointer;
  margin-top: 1.5rem;
  transition: background-color 0.2s;
}

.new-message-btn:hover {
  background-color: #62a682;
}

@media (max-width: 480px) {
  .contact-modal {
    padding: 1.5rem;
  }
  
  h2 {
    font-size: 1.5rem;
  }
  
  .submit-btn {
    width: 100%;
  }
}
</style>