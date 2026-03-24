import Axios from './CallerService';
import type { User } from '@/_models/User';
import type { Kid } from '@/_models/Kid';
import type { Record } from '@/_models/Record';

export async function getAllUsers(): Promise<User[]> {
  try {
    const response = await Axios.get('/admin/users');
    return response.data as User[];
  } catch (error) {
    throw error;
  }
}

export async function deleteUser(userId: number): Promise<void> {
  try {
    await Axios.delete(`/admin/users/delete/${userId}`);
  } catch (error) {
    throw error;
  }
}

export async function updateUser(userId: number, userData: Partial<User>): Promise<User> {
  try {
    const response = await Axios.put(`/admin/users/update/${userId}`, userData);
    return response.data as User;
  } catch (error) {
    throw error;
  }
}

export async function getAllKids(): Promise<Kid[]> {
  try {
    const response = await Axios.get('/admin/kids');
    return response.data as Kid[];
  } catch (error) {
    throw error;
  }
}

export async function getAllRecords(): Promise<Record[]> {
  try {
    const response = await Axios.get('/admin/records');
    return response.data as Record[];
  } catch (error) {
    throw error;
  }
}