<script setup lang="ts">
import { ref } from 'vue';
import router from '@/router';
import { useAuthStore } from '@/stores/auth'; // Import du store d'authentification
import * as AccountService from '@/_services/AccountService';

const authStore = useAuthStore(); // Accéder au store d'authentification

const user = ref({
    email: '',
    password: ''
});

const errorMessage = ref<any>({});

const showPassword = ref(false); // Variable pour basculer la visibilité du mot de passe

async function login() {
    try {
        // Appel de la méthode login du store d'authentification
        await authStore.login(user.value);
        router.push('/'); // Redirection après une connexion réussie
    } catch (error: any) {
        if (error.response && error.response.status === 429) {
            errorMessage.value = {
                general: 'Trop de tentatives, veuillez réessayer ultérieurement.'
            };
        } else {
            errorMessage.value = error.response.data.errors;
        }
    }
}

// Fonction pour basculer la visibilité du mot de passe
function togglePasswordVisibility() {
    showPassword.value = !showPassword.value;
}
</script>


<template>
<div class="form-container">
    <form @submit.prevent="login">
        <h2 class="form-title">Connexion</h2>
        
        <div v-if="errorMessage.general" class="error-message general-error">{{ errorMessage.general }}</div>

        <div class="form-group">
            <label for="user_email">Email</label>
            <input type="text" id="user_email" v-model="user.email" class="input-field"/>
            <div v-if="errorMessage.email" class="error-message" v-for="error in errorMessage.email" :key="error">{{ error }}</div>
        </div>

        <div class="form-group">
            <label for="user_password">Mot de passe</label>
            <div class="password-wrapper">
                <input :type="showPassword ? 'text' : 'password'" id="user_password" v-model="user.password" class="input-field"/>
                <button type="button" class="toggle-password" @click="togglePasswordVisibility">
                    <span v-if="showPassword">👁️</span>
                    <span v-else>👁️‍🗨️</span>
                </button>
            </div>
            <div v-if="errorMessage.password" class="error-message" v-for="error in errorMessage.password" :key="error">{{ error }}</div>
        </div>
        <div v-if="errorMessage.credentials" class="error-message" v-for="error in errorMessage.credentials" :key="error">{{ error }}</div>

        <div class="form-group">
            <button type="submit" class="button">Connexion</button>
        </div>
    </form>
</div>
</template>

<style scoped>
.form-container {
    max-width: 400px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
}

.form-title {
    text-align: center;
    font-size: 1.5em;
    font-weight: bold;
    color: #333;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 16px;
    display: flex;
    flex-direction: column;
}

label {
    font-weight: 500;
    color: #555;
    margin-bottom: 4px;
}

/* Container pour le champ de mot de passe et l'icône */
.password-wrapper {
    display: flex;
    align-items: center;
    position: relative;
}

/* Champ de saisie */
.input-field {
    padding: 10px;
    font-size: 1em;
    border: 1px solid #ccc;
    border-radius: 4px;
    flex: 1;
    transition: border-color 0.3s;
}

.input-field:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
}

/* Bouton pour basculer la visibilité du mot de passe */
.toggle-password {
    background: none;
    border: none;
    font-size: 1.2em;
    cursor: pointer;
    padding: 0 10px;
    color: #666;
    position: absolute;
    right: 10px;
}

.button {
    background-color: #007bff;
    color: #fff;
    padding: 12px;
    font-size: 1em;
    font-weight: bold;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
    text-align: center;
}

.button:hover {
    background-color: #0056b3;
}

.error-message {
    color: #d9534f;
    font-size: 0.9em;
    margin-top: 5px;
}
</style>
