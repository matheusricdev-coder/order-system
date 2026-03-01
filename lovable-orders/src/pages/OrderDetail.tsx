import { useNavigate, useParams } from 'react-router-dom';
import { useOrder, useCancelOrder } from '@/hooks/useOrders';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { toast } from 'sonner';
import type { OrderStatus } from '@/types/api';

const statusConfig: Record<OrderStatus, { label: string; variant: 'default' | 'secondary' | 'destructive' | 'outline' }> = {
  created:         { label: 'Aguardando pagamento', variant: 'secondary' },
  payment_pending: { label: 'Processando pagamento', variant: 'default' },
  paid:            { label: 'Pago',                  variant: 'outline' },
  cancelled:       { label: 'Cancelado',             variant: 'destructive' },
};

export default function OrderDetail() {
  const { orderNumber } = useParams<{ orderNumber: string }>();
  const navigate = useNavigate();
  const { data, isLoading, isError } = useOrder(orderNumber ? parseInt(orderNumber, 10) : null);
  const cancelOrder = useCancelOrder();

  const order = data?.data;

  const handleCancel = async () => {
    if (!order) return;
    if (!window.confirm('Tem certeza que deseja cancelar este pedido?')) return;

    try {
      await cancelOrder.mutateAsync(order.orderNumber);
      toast.success('Pedido cancelado com sucesso.');
    } catch {
      toast.error('Não foi possível cancelar o pedido.');
    }
  };

  if (isLoading) {
    return (
      <div className="flex justify-center items-center min-h-screen">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600" />
      </div>
    );
  }

  if (isError || !order) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center space-y-3">
          <p className="text-red-600">Pedido não encontrado.</p>
          <Button variant="outline" onClick={() => navigate('/orders')}>
            Ver meus pedidos
          </Button>
        </div>
      </div>
    );
  }

  const cfg = statusConfig[order.status];
  const canCancel = order.status === 'created' || order.status === 'payment_pending';

  return (
    <div className="min-h-screen bg-gray-50">
      <header className="bg-white border-b px-6 py-4">
        <div className="max-w-2xl mx-auto flex items-center gap-3">
          <button
            onClick={() => navigate('/orders')}
            className="text-sm text-muted-foreground hover:text-foreground"
          >
            ← Meus pedidos
          </button>
          <h1 className="text-xl font-bold">Pedido</h1>
          <span className="font-mono text-sm text-muted-foreground">
            #{order.orderNumber}
          </span>
        </div>
      </header>

      <main className="max-w-2xl mx-auto px-4 py-8 space-y-6">
        {/* Status */}
        <div className="bg-white rounded-xl border p-6 flex items-center justify-between">
          <div>
            <p className="text-xs text-muted-foreground mb-1">Status</p>
            <Badge variant={cfg.variant}>{cfg.label}</Badge>
          </div>
          {canCancel && (
            <Button
              variant="destructive"
              size="sm"
              onClick={handleCancel}
              disabled={cancelOrder.isPending}
            >
              {cancelOrder.isPending ? 'Cancelando…' : 'Cancelar pedido'}
            </Button>
          )}
        </div>

        {/* Items */}
        <div className="bg-white rounded-xl border p-6">
          <h2 className="font-semibold mb-4">Itens do pedido</h2>
          <div className="space-y-3">
            {order.items.map((item) => (
              <div key={item.productId} className="flex justify-between text-sm">
                <span>
                  {item.name ?? item.productId}
                  <span className="text-muted-foreground ml-1">× {item.quantity}</span>
                </span>
                <span className="font-medium">
                  {(item.unitPrice.amount * item.quantity / 100).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: item.unitPrice.currency,
                  })}
                </span>
              </div>
            ))}
            <div className="border-t pt-3 flex justify-between font-semibold">
              <span>Total</span>
              <span>
                {(order.total.amount / 100).toLocaleString('pt-BR', {
                  style: 'currency',
                  currency: order.total.currency,
                })}
              </span>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}
