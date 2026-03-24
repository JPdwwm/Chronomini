import Axios from './CallerService';
import type { User } from '@/_models/User';
import { getCsrfCookie } from './CallerService';

export async function login(credentials: { email: string; password: string }): Promise<User> {
    await getCsrfCookie();

    const response = await Axios.post('/login', credentials, {
        baseURL: import.meta.env.VITE_API_BASE,
        headers: { 
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
    });

    return response.data.user as User;
}

export async function logout(): Promise<void> {
    await Axios.post('/logout', {}, {
        baseURL: import.meta.env.VITE_API_BASE,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, 
    });

    // Nettoyer les cookies
    const cookies = document.cookie.split(';');
    for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i];
        const eqPos = cookie.indexOf('=');
        const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
        document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/`;
    }
}

