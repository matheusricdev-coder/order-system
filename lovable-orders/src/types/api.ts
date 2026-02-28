// ── Auth ─────────────────────────────────────────────────────────────────────

export interface User {
  id: string;
  name: string;
  surname: string;
  email: string;
  companyId: string | null;
  role: 'customer' | 'admin';
}

export interface AuthToken {
  type: 'Bearer';
  value: string;
}

export interface AuthResponse {
  data: {
    user: User;
    token: AuthToken;
  };
}

// ── Catalog ───────────────────────────────────────────────────────────────────

export interface Money {
  amount: number;   // in cents
  currency: string;
}

export interface Product {
  id: string;
  name: string;
  description?: string;
  categoryId: string;
  categoryName: string | null;
  companyId: string;
  companyName: string | null;
  price: Money;
  promotion: {
    id: string;
    discountPercentage: number;
    originalAmount: number;
    discountedAmount: number;
    endsAt: string;
  } | null;
  images: string[];
}

export interface Category {
  id: string;
  name: string;
}

// ── Orders ────────────────────────────────────────────────────────────────────

export type OrderStatus = 'created' | 'payment_pending' | 'paid' | 'cancelled';

export interface OrderItem {
  productId: string;
  name: string | null;
  quantity: number;
  unitPrice: Money;
  totalPrice: Money;
}

export interface Order {
  id: string;
  userId: string;
  status: OrderStatus;
  total: Money;
  items: OrderItem[];
  createdAt?: string;
  updatedAt?: string;
  paymentIntentId?: string;
  clientSecret?: string;
}

// ── Generic shapes ────────────────────────────────────────────────────────────

export interface ApiResponse<T> {
  data: T;
}

export interface PaginatedMeta {
  total: number;
  perPage: number;
  currentPage: number;
  lastPage: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: PaginatedMeta;
}

export interface WriteMeta {
  correlationId: string | null;
}

export interface WriteResponse<T> {
  data: T;
  meta: WriteMeta;
}

// ── Error ─────────────────────────────────────────────────────────────────────

export interface ApiErrorBody {
  code: string;
  message: string;
  correlation_id: string | null;
  details?: Record<string, string[]>;
}

export class ApiError extends Error {
  constructor(
    public readonly status: number,
    public readonly body: ApiErrorBody,
  ) {
    super(body.message);
    this.name = 'ApiError';
  }
}
