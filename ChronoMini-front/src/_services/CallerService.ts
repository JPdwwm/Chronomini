import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import router from '../router';

const Axios = axios.create({
    baseURL: import.meta.env.VITE_API_BASE + '/api',
    headers: {
        Accept: 'application/json'
    },
    withCredentials: true,
    withXSRFToken: true,
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN'
});

// Fonction pour obtenir le cookie CSRF
export const getCsrfCookie = async () => {
    await Axios.get('/sanctum/csrf-cookie', {
        baseURL: import.meta.env.VITE_API_BASE,
    });
};

Axios.interceptors.response.use(
    response => response,
    async error => {
        // Gestion  des erreurs 401
        if (error.response?.status === 401 && 
            error.config.url !== '/login' && 
            error.code === 'ERR_BAD_REQUEST') {
            
            console.log('🔑 Token invalide détecté, nettoyage du store...');
            const auth = useAuthStore();
            auth.user = null;
            auth.isAuthenticated = false;
            router.push('/register-login');
        }
        
        // Gestion de l'erreur sur les droits admin
        if (error.response?.status === 403 && 
            error.response.data.message === 'Unauthorized. Admin access required.') {
            console.log('👮 Accès administrateur requis');
            
            setTimeout(() => {
                router.push('/unauthorized');
            }, 1000);
        }
        
        return Promise.reject(error);
    }
);



export default Axios;