import Axios from '@/_services/CallerService'; // Utilise l'instance configurée de CallerService
import type { Kid } from '@/_models/Kid';

export async function getKids() {
  try {
    // Axios est déjà configuré pour envoyer automatiquement le cookie CSRF
    const response = await Axios.get('/mykids');
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function createKids(kid: Kid){
  try{
    const response = await Axios.post('/createkid',kid);
    return response.data
  } catch (error){
    throw error;
  }
}

export async function getKidById(id: number) {
  try {
    // Envoie une requête GET à la route `/mykid/{id}`
    const response = await Axios.get(`/mykid/${id}`);
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function deleteKid(id:number) {
  try {
    // Supression de l'enfant
    const response = await Axios.delete(`/deletekid/${id}`) 
    return response.data;
  }catch(error){
    throw error
  }
}

export async function updateKid(id: number, updateData: Partial<Kid>) {
  try {
    const response = await Axios.put(`/updatekid/${id}`, updateData);
    return response.data;
  } catch (error) {
    throw error;
  }
}

