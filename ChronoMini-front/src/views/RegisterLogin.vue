<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router'; 
import { createUser } from '@/_services/UserService'; 
import type { RegisterUser } from '@/_models/RegisterUser';
import { useAuthStore } from '@/stores/auth';
import { toast } from 'vue3-toastify';
import type { LoginUser } from '@/_models/LoginUser';
import 'vue3-toastify/dist/index.css'; 

const authStore = useAuthStore();
const errorMessageLogin = ref<any>({});
const errorMessageRegister = ref<any>({});
const router = useRouter(); 
const showPasswordLogin = ref(false);
const showPasswordRegister = ref(false);
const showPasswordConfirmation = ref(false);
const isSignUpMode = ref(false);
const isMobileView = ref(false);
const isLoggingIn = ref(false);
const isRegistering = ref(false);

// Vérifier si on est en vue mobile au chargement
onMounted(() => {
    checkIfMobile();
    window.addEventListener('resize', checkIfMobile);
});

function checkIfMobile() {
    isMobileView.value = window.innerWidth <= 768;
}

const toggleSignUpMode = () => {
    isSignUpMode.value = true;
};

const toggleSignInMode = () => {
    isSignUpMode.value = false;
};

const registerUser = ref<RegisterUser>({
    last_name: '',
    first_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: 2,
});

const loginUser = ref<LoginUser>({
    email: '',
    password: ''
});

async function login() {
    isLoggingIn.value = true;
    errorMessageLogin.value = {};
    
    try {
        await authStore.login(loginUser.value);
        // Redirection basée sur le rôle
        if (authStore.isAdmin) {
            router.push('/admin');
        } else {
            router.push('/'); // ou toute autre page par défaut pour les utilisateurs normaux
        }
        
        // Afficher le toast de succès
        toast.success(`Connexion réussie !`, {
            position: toast.POSITION.TOP_RIGHT,
            autoClose: 3000,
            progressStyle: { backgroundColor: "#568203" },
            toastStyle: {
            "--toastify-icon-color-success": "#568203", // Change la couleur de la coche
            },
        });
    } catch (error: any) {
        if (error.response && error.response.status === 429) {
            errorMessageLogin.value = {
                general: ['Trop de tentatives, veuillez réessayer ultérieurement.']
            };
        } else if (error.response && error.response.data) {
            errorMessageLogin.value = error.response.data.errors;
        } else {
            errorMessageLogin.value = {
                general: ['Une erreur est survenue lors de la connexion.']
            };
        }
    } finally {
        isLoggingIn.value = false;
    }
}

async function register() {
    isRegistering.value = true;
    errorMessageRegister.value = {};
    
    try {
        await createUser(registerUser.value);
        router.push('/');
        toast.success(`Compte crée avec succès ! Un mail vous à été envoyé pour finaliser votre inscription (Pensez à vérifier les spam)`, {
            position: toast.POSITION.TOP_RIGHT,
            autoClose: 11000,
            progressStyle: { backgroundColor: "#568203" },
            toastStyle: {
            "--toastify-icon-color-success": "#568203", 
            },
        });
    } catch (error: any) {
        if (error.response && error.response.status === 429) {
            errorMessageRegister.value = {
                general: ['Trop de tentatives, veuillez réessayer ultérieurement.']
            };
        } else if (error.response && error.response.data) {
            errorMessageRegister.value = error.response.data.errors;
        } else {
            errorMessageRegister.value = {
                general: ['Une erreur est survenue lors de l\'inscription.']
            };
        }
    } finally {
        isRegistering.value = false;
    }
}

function PasswordVisibilityLogin() {
    showPasswordLogin.value = !showPasswordLogin.value;
}

function PasswordVisibilityRegister() {
    showPasswordRegister.value = !showPasswordRegister.value;
}

function PasswordVisibilityConfirmation() {
    showPasswordConfirmation.value = !showPasswordConfirmation.value;
}
</script>

<template>
    <div class="container" :class="{'sign-up-mode': isSignUpMode, 'mobile-view': isMobileView}">
        <div class="mobile-nav" v-if="isMobileView">
            <button 
                @click="toggleSignInMode" 
                :class="{'active': !isSignUpMode}">
                Connexion
            </button>
            <button 
                @click="toggleSignUpMode" 
                :class="{'active': isSignUpMode}">
                Inscription
            </button>
        </div>

        <div class="forms-container">
            <div class="signin-signup">
                <!-- Formulaire de connexion -->
                <form @submit.prevent="login" class="sign-in-form">
                    <h2 class="title">Connexion</h2>
                    <div class="input-field">
                        <label for="login_user_email">Email</label>
                        <input type="text" id="login_user_email" v-model="loginUser.email" placeholder="Email">
                        <div v-if="errorMessageLogin.email" class="error-message" v-for="error in errorMessageLogin.email" :key="error">{{ error }}</div>
                    </div>
                    <div class="input-field">
                        <label for="login_user_password">Mot de passe</label>
                        <div class="password-wrapper">
                            <input :type="showPasswordLogin ? 'text' : 'password'" id="login_user_password" v-model="loginUser.password" placeholder="Mot de passe">
                            <button type="button" class="toggle-password" @click="PasswordVisibilityLogin" aria-label="Basculer la visibilité du mot de passe">
                                <span v-if="showPasswordLogin"><font-awesome-icon icon="fa-solid fa-eye-slash" /></span>
                                <span v-else><font-awesome-icon icon="fa-solid fa-eye" /></span>
                            </button>
                        </div>
                        <div v-if="errorMessageLogin.password" class="error-message" v-for="error in errorMessageLogin.password" :key="error">{{ error }}</div>
                    </div>
                    <div class="input-field">
                        <button type="submit" class="btn" :disabled="isLoggingIn">
                            <font-awesome-icon v-if="isLoggingIn" icon="fa-solid fa-spinner" class="fa-spin spinner-icon" />
                            {{ isLoggingIn ? 'Connexion en cours...' : 'Se connecter' }}
                        </button>
                    </div>
                    <router-link to="/forgot-password" class="forgot-password-link">
                        Mot de passe oublié ?
                    </router-link>
                    <div v-if="errorMessageLogin.credentials" class="error-message" v-for="error in errorMessageLogin.credentials" :key="error">{{ error }}</div>
                    <div v-if="errorMessageLogin.general" class="error-message" v-for="error in errorMessageLogin.general" :key="error">{{ error }}</div>
                </form>
                
                <!-- Formulaire d'inscription -->
                <form @submit.prevent="register" class="sign-up-form">
                    <h2 class="title">Créer un compte</h2>
                    <div class="input-field">
                        <label for="user_first_name">Prénom</label>
                        <input type="text" id="user_first_name" v-model="registerUser.first_name" placeholder="Prénom">
                        <div v-if="errorMessageRegister.first_name" class="error-message" v-for="error in errorMessageRegister.first_name" :key="error">{{ error }}</div>
                    </div>
                    <div class="input-field">
                        <label for="user_last_name">Nom</label>
                        <input type="text" id="user_last_name" v-model="registerUser.last_name" placeholder="Nom">
                        <div v-if="errorMessageRegister.last_name" class="error-message" v-for="error in errorMessageRegister.last_name" :key="error">{{ error }}</div>
                    </div>
                    <div class="input-field">
                        <label for="register_user_email">Email</label>
                        <input type="email" id="register_user_email" v-model="registerUser.email" placeholder="Email">
                        <div v-if="errorMessageRegister.email" class="error-message" v-for="error in errorMessageRegister.email" :key="error">{{ error }}</div>
                    </div>
                    <div class="input-field">
                        <label for="register_user_password">Mot de passe</label>
                        <div class="password-wrapper">
                            <input :type="showPasswordRegister ? 'text' : 'password'" id="register_user_password" v-model="registerUser.password" placeholder="Mot de passe">
                            <button type="button" class="toggle-password" @click="PasswordVisibilityRegister" aria-label="Basculer la visibilité du mot de passe">
                                <span v-if="showPasswordRegister"><font-awesome-icon icon="fa-solid fa-eye-slash" /></span>
                                <span v-else><font-awesome-icon icon="fa-solid fa-eye" /></span>
                            </button>
                        </div>
                        <div v-if="errorMessageRegister.password" class="error-message" v-for="error in errorMessageRegister.password" :key="error">{{ error }}</div>
                    </div>
                    <div class="input-field">
                        <label for="confirmation_password">Confirmation mot de passe</label>
                        <div class="password-wrapper">
                            <input :type="showPasswordConfirmation ? 'text' : 'password'" id="confirmation_password" v-model="registerUser.password_confirmation" placeholder="Confirmation">
                            <button type="button" class="toggle-password" @click="PasswordVisibilityConfirmation" aria-label="Basculer la visibilité du mot de passe">
                                <span v-if="showPasswordConfirmation"><font-awesome-icon icon="fa-solid fa-eye-slash" /></span>
                                <span v-else><font-awesome-icon icon="fa-solid fa-eye" /></span>
                            </button>
                        </div>
                    </div>
                    <div class="radio-field">
                        <legend></legend>
                        <div class="radio-choice">
                            <input type="radio" id="role_parent" value="2" v-model="registerUser.role_id" />
                            <label for="role_parent">Parent</label>
                        </div>
                        <div class="radio-choice">
                            <input type="radio" id="role_assmat" value="3" v-model="registerUser.role_id" />
                            <label for="role_assmat">Assistante maternelle</label>
                        </div>
                    </div>
                    <div class="consent-field">
                        <div class="checkbox-wrapper">
                            <input 
                                type="checkbox" 
                                id="consent-checkbox" 
                                required
                                class="consent-checkbox"
                            />
                            <label for="consent-checkbox" class="consent-label">
                                J'accepte que mes données personnelles fournies dans ce formulaire soient collectées et traitées pour la gestion des heures de garde. Je comprends que mes données seront utilisées conformément à la 
                                <router-link to="/confid" class="policy-link">politique de confidentialité</router-link>.
                            </label>
                        </div>
                    </div>
                    <div class="input-field">
                        <button type="submit" class="btn" aria-label="Se créer un compte" :disabled="isRegistering">
                            <font-awesome-icon v-if="isRegistering" icon="fa-solid fa-spinner" class="fa-spin spinner-icon" />
                            {{ isRegistering ? 'Création en cours...' : 'Créer son compte' }}
                        </button>
                    </div>
                    <div v-if="errorMessageRegister.credentials" class="error-message" v-for="error in errorMessageRegister.credentials" :key="error">{{ error }}</div>
                    <div v-if="errorMessageRegister.general" class="error-message" v-for="error in errorMessageRegister.general" :key="error">{{ error }}</div>
                </form>
            </div>
        </div>
        <div class="panels-container">
            <div class="panel left-panel">
                <div class="content">
                    <h3>Nouveau ici ? </h3>
                    <button class="btn" @click="toggleSignUpMode" aria-label="Se rendre sur le formulaire de création de compte">Se créer un compte</button>
                </div>
                <img src="/register.svg" class="image" alt="Image d'une femme jouant avec son enfant">
            </div>
            <div class="panel right-panel">
                <div class="content">
                    <h3>De retour ? </h3>
                    <button class="btn" @click="toggleSignInMode" aria-label="Se rendre sur le formulaire de création de connexion">Se connecter</button>
                </div>
                <img src="/log.svg" class="image" alt="Image de trois enfants jouant ensemble">
            </div>
        </div>
    </div>
</template>

<style scoped>
* {
    padding: 0;
    margin: 0;
    box-sizing: border-box;
}

/* Conteneur principal */
.container {
    position: relative;
    width: 100%;
    min-height: calc(100vh - 120px);
    overflow: hidden;
}

.container:before{
    content: '';
    position: absolute;
    width: 2000px;
    height: 2000px;
    border-radius: 50%;
    background: linear-gradient(-45deg, #78BB99, #a0aba6);
    top: -10%;
    right: 48%;
    transform: translateY(-50%);
    transition: 1.8s ease-out;
    z-index: 6;
}

.forms-container {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
}

.signin-signup {
    position: absolute;
    top: 50%;
    left: 75%;
    transform: translate(-50%, -50%);
    width: 50%;
    display: grid;
    grid-template-columns: 1fr;
    z-index: 5;
    transition: 1s 0.7s ease-in-out;
    max-height: 87vh; 
    overflow-y: auto;
    scrollbar-width: thin; /* Pour Firefox */
    scrollbar-color: rgba(53, 142, 157, 0.5) transparent; /* Pour Firefox */
}

.signin-signup::-webkit-scrollbar {
    width: 8px;
}

.signin-signup::-webkit-scrollbar-track {
    background: transparent;
}

.signin-signup::-webkit-scrollbar-thumb {
    background-color: rgba(53, 142, 157, 0.5);
    border-radius: 10px;
}

/* Formulaires principaux */
form {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    overflow: hidden;
    padding: 0 5rem;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 100%;
    flex-direction: column;
    grid-column: 1 / 2;
    grid-row: 1 / 2;
    transition: 0.2s 0.7s ease-in-out;
}

form.sign-in-form{
    z-index: 2;
}

form.sign-up-form{
    z-index: 1;
    opacity: 0;
}

.title {
    font-size: 2.2rem;
    color: #358E9D;
    margin-bottom: 20px;
}

label {
    font-weight: 500;
    color: #555;
    margin-bottom: 4px;
}

/* Champ d'entrée avec son label */
.input-field {
    max-width: 400px;
    width: 100%;
    margin-bottom: 16px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 0 .4rem;
    border-radius: 15px;
    position: relative;
    box-sizing: border-box;
}

.input-field input {
    padding: 10px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 100%;
    box-sizing: border-box;
}

.input-field:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
}

.input-field label {
    color: #333;
    margin-bottom: 8px;
}

.password-wrapper {
    display: flex;
    align-items: center;
    position: relative;
    width: 100%;
}

.password-wrapper input {
    padding: 10px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 100%;
    box-sizing: border-box;
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

.btn {
    width: 100%;
    max-width: 400px;
    padding: 10px;
    background-color: #358E9D;
    color: white;
    font-weight: bold;
    font-size: 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-align: center;
    transition: background-color 0.3s;
    margin-top: 16px;
}

.btn:hover {
    background-color: #286C72;
}

.btn:disabled {
    background-color: #a0aba6;
    cursor: not-allowed;
}

/* Style de l'icône de chargement */
.spinner-icon {
    margin-right: 8px;
}

.radio-field {
    display: flex;
    justify-content: flex-start;
    gap: 20px;
    width: 100%;
    max-width: 400px;
    margin-bottom: 16px;
}

.radio-field input {
    margin-right: 5px;
}

.radio-choice {
    margin-right: 20px;
    display: flex;
    align-items: center;
}

.consent-field {
    max-width: 400px;
    width: 100%;
    margin-bottom: 16px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 0 .4rem;
    position: relative;
    box-sizing: border-box;
}

.checkbox-wrapper {
    display: flex;
    align-items: flex-start;
    margin-bottom: 5px;
    width: 100%;
}

.consent-checkbox {
    margin-top: 4px;
    margin-right: 10px;
    cursor: pointer;
}

.consent-label {
    font-size: 0.9rem;
    color: #666;
    line-height: 1.4;
    text-align: left;
}

.policy-link {
    color: #358E9D;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.2s;
}

.policy-link:hover {
    color: #286C72;
    text-decoration: underline;
}

.error-message {
    color: #d9534f;
    font-size: 0.9em;
    margin-top: 5px;
    max-width: 100%;
    word-wrap: break-word;
    text-align: left;
}

.forgot-password-link {
    margin-top: 10px;
    color: #358E9D;
    text-decoration: none;
}

.forgot-password-link:hover {
    text-decoration: underline;
}

.panels-container{
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
}

.panel{
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    text-align: center;
    z-index: 7;
}

.panel .content{
    color: #fff;
    transition: .9s .6s;
}

.left-panel{
    padding: 3rem 12% 2rem 17%;
    pointer-events: all;
}

.right-panel{
    padding: 3rem 12% 2rem 17%;
    pointer-events: none;
}

.image{
    width: 100%;
    transition: 1.1s .4s ease-in-out;
}

.right-panel .content, .right-panel .image{
    transform: translateX(800px);
}

/* animation */
.container.sign-up-mode:before{
    transform: translate(100%, -50%);
    right: 52%;
}

.container.sign-up-mode .left-panel .image,
.container.sign-up-mode .left-panel .content{
    transform: translateX(-800px);
} 

.container.sign-up-mode .right-panel .content, 
.container.sign-up-mode .right-panel .image{
    transform: translateX(0px);
}

.container.sign-up-mode .left-panel{
    pointer-events: none;
}

.container.sign-up-mode .right-panel{
    pointer-events: all;
}

.container.sign-up-mode .signin-signup{
    left: 25%;
}

.container.sign-up-mode form.sign-in-form{
    z-index: 1;
    opacity: 0;
}

.container.sign-up-mode form.sign-up-form{
    z-index: 2;
    opacity: 1;
}

/* Navigation mobile */
.mobile-nav {
    display: none;
}

/* MEDIA QUERIES POUR LE RESPONSIVE */
@media (max-width: 768px) {
    .container.mobile-view {
        min-height: 100vh;
        padding-bottom: 60px;
        overflow-x: hidden;
        overflow-y: auto;
    }

    .container.mobile-view:before {
        display: none;
    }


    .mobile-nav {
        display: flex;
        justify-content: center;
        margin: 20px 0;
        z-index: 20;
        position: relative;
    }

    .mobile-nav button {
        padding: 8px 16px;
        margin: 0 10px;
        background-color: #ddd;
        color: #333;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .mobile-nav button.active {
        background-color: #358E9D;
        color: white;
    }

    .panels-container {
        display: none;
    }

    .signin-signup {
        position: relative;
        top: 0;
        left: 0;
        transform: none;
        width: 100%;
        padding: 20px;
        margin: 0 auto;
        max-width: 500px;
        height: auto;
    }

    form {
        padding: 2rem;
        margin-bottom: 30px;
        background-color: white;
    }

    .consent-label {
    font-size: 0.85rem;
    } 

    .container.mobile-view.sign-up-mode .signin-signup {
        left: 0;
    }

    .container.mobile-view form.sign-in-form {
        display: flex;
        opacity: 1;
        z-index: 2;
    }
    
    .container.mobile-view form.sign-up-form {
        display: none;
        opacity: 0;
        z-index: 1;
    }
    
    .container.mobile-view.sign-up-mode form.sign-in-form {
        display: none;
    }
    
    .container.mobile-view.sign-up-mode form.sign-up-form {
        display: flex;
        opacity: 1;
        z-index: 2;
    }

    .input-field {
        padding: 0;
    }

    .title {
        font-size: 1.8rem;
    }
}

@media (max-width: 425px) {
    form {
        padding: 1.5rem;
    }

    .consent-label {
        font-size: 0.8rem;
    }
}
</style>