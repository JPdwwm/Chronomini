import Axios from './CallerService';
import type { ConnectionRequest } from '@/_models/ConnectionRequest';

export async function connectUsers(data: { link_code: string }): Promise<any> {
  try {
    const response = await Axios.post('/connection-request', data);
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function getReceivedConnectionRequests(): Promise<ConnectionRequest[]> {
  try {
    const response = await Axios.get('/connection-requests/received');
    return response.data.requests;
  } catch (error) {
    throw error;
  }
}

export async function getSentConnectionRequests(): Promise<ConnectionRequest[]> {
  try {
    const response = await Axios.get('/connection-requests/sent');
    return response.data.requests;
  } catch (error) {
    throw error;
  }
}

export async function acceptConnectionRequest(requestId: number): Promise<any> {
  try {
    const response = await Axios.post(`/connection-requests/${requestId}/accept`);
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function declineConnectionRequest(requestId: number): Promise<any> {
  try {
    const response = await Axios.post(`/connection-requests/${requestId}/decline`);
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function mergeKids(data: { kid_to_keep: number, kid_to_remove: number }): Promise<any> {
  try {
    const response = await Axios.post('/merge-kids', data);
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function getConnectedUsers(): Promise<any[]> {
  try {
    const response = await Axios.get('/connected-users');
    return response.data.connected_users;
  } catch (error) {
    throw error;
  }
}

export async function disconnectUser(userId: number): Promise<any> {
  try {
    const response = await Axios.post('/disconnect-user', { user_id: userId });
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function checkDuplicatesWithUser(userId: number): Promise<any> {
  try {
    const response = await Axios.get(`/check-duplicates-with-user/${userId}`);
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function shareKidWithPartner(data: { kid_id: number, partner_id: number }): Promise<any> {
  try {
    const response = await Axios.post('/share-kid-with-partner', data);
    return response.data;
  } catch (error) {
    throw error;
  }
}
