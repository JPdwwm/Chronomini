<script setup lang="ts">
import { onMounted, ref, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import * as VerificationService from '@/_services/VerificationService';

const route = useRoute();
const router = useRouter();

const verificationStatus = ref<'loading' | 'success' | 'error' | 'already-verified'>('loading');
const verificationError = ref<string | null>(null);
const redirectCountdown = ref(10);
let countdownInterval: number | null = null;

async function verification(email: string, token: string) {
    try {
        const response = await VerificationService.verification(email, token);
        
        // Vérifier si l'email est déjà vérifié
        if (response.alreadyVerified) {
            verificationStatus.value = 'already-verified';
        } else {
            verificationStatus.value = 'success';
            
            // Démarrer le compte à rebours pour la redirection
            countdownInterval = setInterval(() => {
                redirectCountdown.value--;
                if (redirectCountdown.value <= 0) {
                    clearCountdown();
                    router.push('/register-login');
                }
            }, 1000) as unknown as number;
        }
    } catch (error) {
        verificationStatus.value = 'error';
        verificationError.value = 'La vérification a échoué, veuillez réessayer plus tard.';
    }
}

// Nouvelle fonction pour arrêter le compte à rebours
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

onMounted(async () => {
    const email = route.query.email as string;
    const token = route.query.token as string;

    if (email && token) {
        await verification(email, token);
    } else {
        verificationStatus.value = 'error';
        verificationError.value = 'Les paramètres nécessaires sont manquants.';
    }
});

// Nettoyage correct au démontage du composant
onBeforeUnmount(() => {
    clearCountdown();
});
</script>

<template>
    <main>
        <div class="verification-container">
            <!-- État de chargement -->
            <div v-if="verificationStatus === 'loading'" class="loading-state">
                <div class="spinner"></div>
                <h2>Vérification en cours...</h2>
                <p>Nous sommes en train de vérifier votre adresse email.</p>
            </div>

            <!-- État de succès -->
            <div v-if="verificationStatus === 'success'" class="success-state">
                <div class="success-icon">✓</div>
                <h1>Félicitations !</h1>
                <p>Votre email a été vérifié avec succès. Vous pouvez désormais vous connecter à ChronoMini.</p>
                <p class="redirect-message">Vous serez redirigé vers la page de connexion dans <span class="countdown">{{ redirectCountdown }}</span> secondes.</p>
                <button class="login-button" @click="redirectToLogin">
                    Se connecter maintenant
                </button>
            </div>

            <div v-if="verificationStatus === 'already-verified'" class="already-verified-state">
                <div class="info-icon">i</div>
                <h1>Email déjà vérifié</h1>
                <p>Votre adresse email a déjà été vérifiée. Vous pouvez vous connecter à ChronoMini.</p>
                <button class="login-button" @click="router.push('/register-login')">
                    Se connecter
                </button>
            </div>

            <!-- État d'erreur -->
            <div v-if="verificationStatus === 'error'" class="error-state">
                <div class="error-icon">!</div>
                <h1>Oups !</h1>
                <div class="alert-message">{{ verificationError }}</div>
                <p>Il semble y avoir un problème avec la vérification de votre email.</p>
                <button class="retry-button" @click="router.push('/register-login')">
                    Retour à la page de connexion
                </button>
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

.verification-container {
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

.loading-state .spinner {
    border: 4px solid rgba(0, 0, 0, 0.1);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border-left-color: #358E9D;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

.info-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0 auto 20px;
    font-size: 40px;
    font-weight: bold;
    background-color: #78BB99;
    color: white;
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

.login-button, .retry-button {
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

.login-button:hover, .retry-button:hover {
    background-color: #286C72;
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
    .verification-container {
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