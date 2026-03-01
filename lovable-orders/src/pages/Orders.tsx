import { Link, useNavigate } from 'react-router-dom';
import { useOrders } from '@/hooks/useOrders';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { OrderStatus } from '@/types/api';

const statusConfig: Record<OrderStatus, { label: string; variant: 'default' | 'secondary' | 'destructive' | 'outline' }> = {
  created:         { label: 'Aguardando pagamento', variant: 'secondary' },
  payment_pending: { label: 'Processando pagamento', variant: 'default' },
  paid:            { label: 'Pago',                  variant: 'outline' },
  cancelled:       { label: 'Cancelado',             variant: 'destructive' },
};

export default function Orders() {
  const navigate = useNavigate();
  const { data, isLoading, isError } = useOrders();

  const orders = data?.data ?? [];

  return (
    <div className="min-h-screen bg-gray-50">
      <header className="bg-white border-b px-6 py-4">
        <div className="max-w-3xl mx-auto flex items-center justify-between">
          <div className="flex items-center gap-3">
            <button
              onClick={() => navigate('/')}
              className="text-sm text-muted-foreground hover:text-foreground"
            >
              ← Catálogo
            </button>
            <h1 className="text-xl font-bold">Meus Pedidos</h1>
          </div>
        </div>
      </header>

      <main className="max-w-3xl mx-auto px-4 py-8">
        {isLoading && (
          <div className="flex justify-center py-16">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600" />
          </div>
        )}

        {isError && (
          <div className="text-center py-16 text-red-600">
            Erro ao carregar pedidos. Tente novamente.
          </div>
        )}

        {!isLoading && orders.length === 0 && (
          <div className="text-center py-20 space-y-4">
            <p className="text-muted-foreground">Você ainda não fez nenhum pedido.</p>
            <Button onClick={() => navigate('/')}>Explorar produtos</Button>
          </div>
        )}

        <div className="space-y-4">
          {orders.map((order) => {
            const cfg = statusConfig[order.status];
            const totalFormatted = (order.total.amount / 100).toLocaleString('pt-BR', {
              style: 'currency',
              currency: order.total.currency,
            });

            return (
              <Link
                key={order.id}
                to={`/orders/${order.orderNumber}`}
                className="block bg-white rounded-xl border hover:border-indigo-400 transition-colors p-5"
              >
                <div className="flex items-start justify-between gap-4">
                  <div className="space-y-1">
                    <p className="font-mono text-xs text-muted-foreground">
                      Pedido #{order.orderNumber}
                    </p>
                    <p className="text-sm text-muted-foreground">
                      {order.items.length} {order.items.length === 1 ? 'item' : 'itens'}
                    </p>
                  </div>
                  <div className="text-right space-y-1">
                    <Badge variant={cfg.variant}>{cfg.label}</Badge>
                    <p className="text-sm font-semibold">{totalFormatted}</p>
                  </div>
                </div>
              </Link>
            );
          })}
        </div>
      </main>
    </div>
  );
}
