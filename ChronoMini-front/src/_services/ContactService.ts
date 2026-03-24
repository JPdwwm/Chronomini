import Axios from './CallerService';
import type { ContactForm } from '@/_models/ContactForm';
import { getCsrfCookie } from './CallerService';


export async function sendContactMessage(contactData: ContactForm): Promise<void> {
  try {
    // Récupération du cookie CSRF pour les formulaires
    await getCsrfCookie();
    
    // Envoi de la requête au point de terminaison du formulaire de contact
    await Axios.post('/contact', contactData, {
      baseURL: import.meta.env.VITE_API_BASE,
      headers: { 'Content-Type': 'application/json' }
    });
  } catch (error) {
    throw error;
  }
}