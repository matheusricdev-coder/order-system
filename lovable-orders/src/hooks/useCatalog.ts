import { useQuery } from '@tanstack/react-query';
import { authApi, catalogApi, type ProductFilters } from '@/lib/api';

export function useLoginStreak(enabled = true) {
  return useQuery({
    queryKey: ['login-streak'],
    queryFn: () => authApi.loginStreak(),
    enabled,
    staleTime: 1000 * 60 * 5,
  });
}

export function useProducts(filters: ProductFilters = {}) {
  return useQuery({
    queryKey: ['products', filters],
    queryFn: () => catalogApi.products(filters),
    staleTime: 1000 * 60 * 5, // 5 min
  });
}

export function useProduct(id: string) {
  return useQuery({
    queryKey: ['product', id],
    queryFn: () => catalogApi.product(id),
    enabled: Boolean(id),
    staleTime: 1000 * 60 * 5,
  });
}

export function useCategories() {
  return useQuery({
    queryKey: ['categories'],
    queryFn: () => catalogApi.categories(),
    staleTime: 1000 * 60 * 30, // 30 min — rarely changes
  });
}
