import Axios from './CallerService';
import { getCsrfCookie } from '@/_services/CallerService';

// Demande d'email de réinitialisation
export async function requestPasswordReset(email: string): Promise<any> {
  try {
    await getCsrfCookie();
    const response = await Axios.post('/forgot-password', { email }, {
      baseURL: import.meta.env.VITE_API_BASE,
      headers: { 
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });
    return response.data;
  } catch (error: any) {
    if (error.response?.status === 422) {
      throw { 
        validationErrors: error.response.data.errors,
        message: error.response.data.message 
      };
    }
    throw error;
  }
}

// Vérifier la validité du token
export async function verifyResetToken(email: string, token: string): Promise<any> {
  await getCsrfCookie();
  const response = await Axios.post('/verify-reset-token', { email, token }, {
    baseURL: import.meta.env.VITE_API_BASE,
    headers: { 
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    }
  });
  return response.data;
}

// Réinitialiser le mot de passe
export async function resetPassword(email: string, token: string, password: string, password_confirmation: string): Promise<any> {
  await getCsrfCookie();
  const response = await Axios.post('/reset-password', { 
    email, 
    token, 
    password, 
    password_confirmation 
  }, {
    baseURL: import.meta.env.VITE_API_BASE,
    headers: { 
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    }
  });
  return response.data;
}