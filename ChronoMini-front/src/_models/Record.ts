export interface Record {
  id?: number;
  kid_id: number;
  user_id?: number;
  drop_hour?: string;
  pick_up_hour?: string;
  date?: string;
  amount_hours?: number;
  annotation?: string;
}