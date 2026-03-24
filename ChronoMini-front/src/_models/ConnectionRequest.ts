export interface ConnectionRequest {
  id: number;
  sender_id: number;
  receiver_id: number;
  status: 'pending' | 'accepted' | 'declined';
  sender?: {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    role: {
      id: number;
      name: string;
    }
  };
  receiver?: {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    role: {
      id: number;
      name: string;
    }
  };
  partner?: {
    id: number;
    name: string;
  }; 
  created_at?: string;
  updated_at?: string;
}