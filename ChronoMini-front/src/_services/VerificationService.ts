import Axios from './CallerService';
import type { VerificationResponse } from '@/_models/VerificationResponse';

//Email verification function for new user.
export async function verification(email: string, token: string): Promise<VerificationResponse> {
    const response = await Axios.post('/verification', { email, token });
    return response.data;
  }