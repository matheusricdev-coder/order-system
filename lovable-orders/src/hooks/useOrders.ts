import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { ordersApi } from '@/lib/api';

export function useOrders(params: { status?: string; page?: number } = {}) {
  return useQuery({
    queryKey: ['orders', params],
    queryFn: () => ordersApi.list(params),
    staleTime: 0,
  });
}

export function useOrder(orderNumber: number | null) {
  return useQuery({
    queryKey: ['order', orderNumber],
    queryFn: () => ordersApi.get(orderNumber!),
    enabled: orderNumber !== null && orderNumber > 0,
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
    mutationFn: (orderNumber: number) => ordersApi.pay(orderNumber),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['orders'] }),
  });
}

export function useCancelOrder() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (orderNumber: number) => ordersApi.cancel(orderNumber),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['orders'] }),
  });
}
