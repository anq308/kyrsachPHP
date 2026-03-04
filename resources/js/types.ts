export interface User {
  id: number;
  name: string;
  email: string;
  is_admin: boolean;
  created_at?: string;
}

export interface Motorcycle {
  id: number;
  brand: string;
  model: string;
  type: string;
  year: number;
  engine_capacity: number;
  power: number;
  price: number;
  description: string;
  image_url: string;
  is_available: boolean;
  transmission?: string | null;
  cooling?: string | null;
  fuel_system?: string | null;
  weight?: number | null;
  tank_capacity?: number | null;
  views_count?: number;
}

export interface NewsItem {
  title: string;
  date: string;
  image: string;
  excerpt: string;
}

export interface CartItem {
  id: number | string;
  name: string;
  quantity: number;
  price: number;
  image: string;
  line_total: number;
}

export interface CartPayload {
  items: CartItem[];
  total: number;
  count: number;
}

export interface OrderItem {
  id: number;
  order_id: number;
  motorcycle_id: number | null;
  name: string;
  price: number;
  quantity: number;
}

export interface Order {
  id: number;
  user_id: number | null;
  name: string;
  phone: string;
  email: string | null;
  address: string | null;
  comment: string | null;
  total: number;
  status: 'new' | 'processing' | 'completed' | 'cancelled';
  items: OrderItem[];
  created_at: string;
}
