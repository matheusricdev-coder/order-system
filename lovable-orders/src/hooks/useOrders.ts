import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ordersApi } from '@/lib/api';

export function useOrders(params: { status?: string; page?: number } = {}) {
  return useQuery({
    queryKey: ['orders', params],
    queryFn: () => ordersApi.list(params),
    staleTime: 0,
  });
}

export function useOrder(id: string) {
  return useQuery({
    queryKey: ['order', id],
    queryFn: () => ordersApi.get(id),
    enabled: Boolean(id),
    staleTime: 0,
  });
}

export function useCreateOrder() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (items: { productId: string; quantity: number }[]) =>
      ordersApi.create(items),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['orders'] }),
  });
}

export function usePayOrder() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => ordersApi.pay(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['orders'] }),
  });
}

export function useCancelOrder() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => ordersApi.cancel(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['orders'] }),
  });
}
