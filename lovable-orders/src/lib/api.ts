import { ApiError, ApiErrorBody } from '@/types/api';

// In development the Vite proxy forwards /api/* → http://127.0.0.1:8000,
// so an empty env var is fine — we fall back to the relative path /api/v1.
// In production set VITE_API_BASE_URL=https://ordem-system-api.fly.dev/api/v1
const BASE_URL: string =
  (import.meta.env.VITE_API_BASE_URL as string | undefined) || '/api/v1';

const TOKEN_KEY = 'auth_token';

export function getStoredToken(): string | null {
  return localStorage.getItem(TOKEN_KEY);
}

export function setStoredToken(token: string): void {
  localStorage.setItem(TOKEN_KEY, token);
}

export function clearStoredToken(): void {
  localStorage.removeItem(TOKEN_KEY);
}

// ── Core fetch wrapper ────────────────────────────────────────────────────────

async function request<T>(
  method: string,
  path: string,
  body?: unknown,
): Promise<T> {
  const token = getStoredToken();

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const res = await fetch(`${BASE_URL}${path}`, {
    method,
    headers,
    body: body !== undefined ? JSON.stringify(body) : undefined,
  });

  if (res.status === 204) {
    return undefined as T;
  }

  const contentType = res.headers.get('content-type') ?? '';
  const hasJsonBody = contentType.includes('application/json');
  const json = hasJsonBody ? await res.json() : null;

  if (!res.ok) {
    if (json && typeof json === 'object' && 'error' in json) {
      throw new ApiError(res.status, json.error as ApiErrorBody);
    }

    throw new ApiError(res.status, {
      code: 'unknown_error',
      message: `Request failed with status ${res.status}`,
      correlation_id: null,
    });
  }

  return json as T;
}

// ── Convenience helpers ───────────────────────────────────────────────────────

export const api = {
  get: <T>(path: string) => request<T>('GET', path),
  post: <T>(path: string, body?: unknown) => request<T>('POST', path, body),
  put: <T>(path: string, body?: unknown) => request<T>('PUT', path, body),
  delete: <T>(path: string) => request<T>('DELETE', path),
};

// ── Auth endpoints ────────────────────────────────────────────────────────────

import type { AuthResponse, ApiResponse, User } from '@/types/api';

export const authApi = {
  login: (email: string, password: string) =>
    api.post<AuthResponse>('/auth/login', { email, password }),

  register: (payload: {
    name: string;
    surname: string;
    birth_date: string;
    email: string;
    password: string;
    password_confirmation: string;
  }) => api.post<AuthResponse>('/auth/register', payload),

  me: () => api.get<ApiResponse<User>>('/me'),

  loginStreak: () =>
    api.get<ApiResponse<{ weekDays: boolean[]; totalCoins: number }>>('/me/login-streak'),

  logout: () => api.post<void>('/auth/logout'),
};

// ── Catalog endpoints ─────────────────────────────────────────────────────────

import type { Product, Category, PaginatedResponse } from '@/types/api';

export type SortBy  = 'name' | 'price';
export type SortDir = 'asc' | 'desc';

export interface ProductFilters {
  categoryId?: string;
  companyId?: string;
  q?: string;
  /** Minimum price in cents */
  minPrice?: number;
  /** Maximum price in cents */
  maxPrice?: number;
  sortBy?: SortBy;
  sortDir?: SortDir;
  perPage?: number;
  page?: number;
  onlyWithPromotion?: boolean;
}

export const catalogApi = {
  products: (filters: ProductFilters = {}) => {
    const params = new URLSearchParams();
    if (filters.categoryId)              params.set('categoryId', filters.categoryId);
    if (filters.companyId)               params.set('companyId', filters.companyId);
    if (filters.q)                       params.set('q', filters.q);
    if (filters.minPrice !== undefined)  params.set('minPrice', String(filters.minPrice));
    if (filters.maxPrice !== undefined)  params.set('maxPrice', String(filters.maxPrice));
    if (filters.sortBy)                  params.set('sortBy', filters.sortBy);
    if (filters.sortDir)                 params.set('sortDir', filters.sortDir);
    if (filters.perPage)                 params.set('perPage', String(filters.perPage));
    if (filters.page)                    params.set('page', String(filters.page));
    if (filters.onlyWithPromotion)       params.set('onlyWithPromotion', '1');
    const qs = params.toString();
    return api.get<PaginatedResponse<Product>>(`/products${qs ? `?${qs}` : ''}`);
  },

  product: (slug: string) =>
    api.get<{ data: Product }>(`/products/${slug}`),

  categories: () =>
    api.get<{ data: Category[] }>('/categories'),
};

// ── Orders endpoints ──────────────────────────────────────────────────────────

import type { Order, WriteResponse } from '@/types/api';

export const ordersApi = {
  create: (items: { productId: string; quantity: number }[]) =>
    api.post<WriteResponse<Order>>('/orders', { items }),

  list: (params: { status?: string; page?: number; perPage?: number } = {}) => {
    const qs = new URLSearchParams();
    if (params.status) qs.set('status', params.status);
    if (params.page) qs.set('page', String(params.page));
    if (params.perPage) qs.set('perPage', String(params.perPage));
    const q = qs.toString();
    return api.get<PaginatedResponse<Order>>(`/orders${q ? `?${q}` : ''}`);
  },

  get: (orderNumber: number) =>
    api.get<{ data: Order }>(`/orders/${orderNumber}`),

  pay: (orderNumber: number) =>
    api.post<WriteResponse<Order>>(`/orders/${orderNumber}/pay`),

  cancel: (orderNumber: number) =>
    api.post<WriteResponse<Order>>(`/orders/${orderNumber}/cancel`),
};

// ── Admin endpoints ───────────────────────────────────────────────────────────

const ADMIN_BASE = '/admin';

export interface AdminProduct {
  id: string;
  name: string;
  description: string | null;
  categoryId: string;
  categoryName: string | null;
  companyId: string;
  companyName: string | null;
  price: { amount: number; currency: string };
  images: string[];
  createdAt: string;
  updatedAt: string;
}

export interface AdminStock {
  id: string;
  productId: string;
  quantityTotal: number;
  quantityReserved: number;
  quantityAvailable: number;
  updatedAt: string;
}

export const adminApi = {
  // Products
  listProducts: (params: { page?: number; perPage?: number } = {}) => {
    const qs = new URLSearchParams();
    if (params.page) qs.set('page', String(params.page));
    if (params.perPage) qs.set('perPage', String(params.perPage));
    const q = qs.toString();
    return api.get<PaginatedResponse<AdminProduct>>(`${ADMIN_BASE}/products${q ? `?${q}` : ''}`);
  },

  createProduct: (payload: {
    name: string;
    description?: string;
    categoryId: string;
    companyId: string;
    priceAmount: number;
    priceCurrency?: string;
  }) => api.post<{ data: AdminProduct }>(`${ADMIN_BASE}/products`, payload),

  updateProduct: (id: string, payload: Partial<{
    name: string;
    description: string;
    categoryId: string;
    priceAmount: number;
  }>) => api.put<{ data: AdminProduct }>(`${ADMIN_BASE}/products/${id}`, payload),

  deleteProduct: (id: string) =>
    api.delete<void>(`${ADMIN_BASE}/products/${id}`),

  // Stock
  listStocks: () =>
    api.get<PaginatedResponse<AdminStock>>(`${ADMIN_BASE}/stocks`),

  updateStock: (productId: string, quantityTotal: number) =>
    api.put<{ data: AdminStock }>(`${ADMIN_BASE}/stocks/${productId}`, { quantityTotal }),

  // Orders
  listOrders: (params: { status?: string; page?: number; perPage?: number } = {}) => {
    const qs = new URLSearchParams();
    if (params.status) qs.set('status', params.status);
    if (params.page) qs.set('page', String(params.page));
    if (params.perPage) qs.set('perPage', String(params.perPage));
    const q = qs.toString();
    return api.get<PaginatedResponse<Order>>(`${ADMIN_BASE}/orders${q ? `?${q}` : ''}`);
  },

  getOrder: (id: string) =>
    api.get<{ data: Order }>(`${ADMIN_BASE}/orders/${id}`),
};
