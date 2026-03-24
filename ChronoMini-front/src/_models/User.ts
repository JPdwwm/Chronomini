export interface User {
  id?: number;
  email: string;
  password?: string;
  first_name?: string;
  last_name?: string;
  role_id?: number;
  zip_code?: string; 
  city?: string;
  password_confirmation?: string;
  link_code?: string;
}
