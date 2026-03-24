import Axios from './CallerService'
import type { User } from '@/_models/User';
import { getCsrfCookie } from './CallerService';


export async function getUser(): Promise<User> {
  try {
      // Appel à l'API pour récupérer les informations du profil de l'utilisateur
      const response = await Axios.get('/user', {
      });
      return response.data as User;  // Retourne les données de l'utilisateur en les typant comme User
  } catch (error) {
      throw error;
  }
}

export async function updateUser(user:Partial<User>): Promise<void> {
  return await Axios.put('/user', user);
}


export async function createUser(user: User): Promise<void>{
  // Utiliser la fonction importée au lieu de l'appel direct  
  await getCsrfCookie();
  
  try {
    await Axios.post('/register', user, { 
      headers: { 'Content-Type': 'application/json' }  
    }); 
  } catch (error) {
    throw error;
  }
}

export async function deleteUser(): Promise<any> {
  return await Axios.delete('/user');
}