import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { loadStripe } from '@stripe/stripe-js';
import {
  Elements,
  PaymentElement,
  useStripe,
  useElements,
} from '@stripe/react-stripe-js';
import { useCart } from '@/contexts/CartContext';
import { useCreateOrder, usePayOrder } from '@/hooks/useOrders';
import { Button } from '@/components/ui/button';
import { toast } from 'sonner';
import type { Order } from '@/types/api';

// Load Stripe once outside the component
const stripePromise = loadStripe(import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY as string);

// ── Inner form (needs Stripe context) ────────────────────────────────────────

function CheckoutForm({ order }: { order: Order }) {
  const stripe = useStripe();
  const elements = useElements();
  const navigate = useNavigate();
  const { clearCart } = useCart();
  const [isProcessing, setIsProcessing] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!stripe || !elements) return;

    setIsProcessing(true);

    const { error } = await stripe.confirmPayment({
      elements,
      confirmParams: {
        return_url: `${window.location.origin}/orders/${order.id}?payment=success`,
      },
      redirect: 'if_required',
    });

    if (error) {
      toast.error(error.message ?? 'Falha no pagamento. Tente novamente.');
      setIsProcessing(false);
      return;
    }

    // Payment succeeded (no redirect needed — e.g. card payment)
    clearCart();
    toast.success('Pagamento realizado com sucesso!');
    navigate(`/orders/${order.id}`);
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="bg-white rounded-xl border p-6">
        <h2 className="text-lg font-semibold mb-4">Dados do pagamento</h2>
        <PaymentElement />
      </div>

      {/* Order Summary */}
      <div className="bg-white rounded-xl border p-6">
        <h2 className="text-lg font-semibold mb-3">Resumo</h2>
        <div className="space-y-2 text-sm">
          {order.items.map((item) => (
            <div key={item.productId} className="flex justify-between">
              <span>{item.name ?? item.productId} × {item.quantity}</span>
              <span>
                {(item.unitPrice.amount * item.quantity / 100).toLocaleString('pt-BR', {
                  style: 'currency',
                  currency: item.unitPrice.currency,
                })}
              </span>
            </div>
          ))}
          <div className="border-t pt-2 font-semibold flex justify-between">
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

      <Button
        type="submit"
        disabled={!stripe || isProcessing}
        className="w-full"
        size="lg"
      >
        {isProcessing ? 'Processando…' : 'Confirmar pagamento'}
      </Button>
    </form>
  );
}

// ── Main page ─────────────────────────────────────────────────────────────────

export default function Checkout() {
  const navigate = useNavigate();
  const { items: cartItems, clearCart } = useCart();
  const createOrder = useCreateOrder();
  const payOrder = usePayOrder();

  const [order, setOrder] = useState<Order | null>(null);
  const [clientSecret, setClientSecret] = useState<string | null>(null);
  const [isInitializing, setIsInitializing] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // On mount: create order then initiate payment intent
  useEffect(() => {
    if (cartItems.length === 0) {
      navigate('/');
      return;
    }

    const init = async () => {
      try {
        // 1. Create the order
        const createResponse = await createOrder.mutateAsync(
          cartItems.map((item) => ({ productId: item.productId, quantity: item.quantity })),
        );
        const createdOrder = createResponse.data;

        // 2. Initiate payment (gets client_secret from Stripe via backend)
        const payResponse = await payOrder.mutateAsync(createdOrder.id);
        const payingOrder = payResponse.data;

        if (!payingOrder.clientSecret) {
          throw new Error('Nenhum client secret retornado pelo servidor.');
        }

        setOrder(payingOrder);
        setClientSecret(payingOrder.clientSecret);
      } catch (err: unknown) {
        const msg = err instanceof Error ? err.message : 'Erro ao iniciar checkout.';
        setError(msg);
        toast.error(msg);
      } finally {
        setIsInitializing(false);
      }
    };

    void init();
    // Run only on mount — cartItems reference stable inside closure
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (isInitializing) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center space-y-3">
          <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mx-auto" />
          <p className="text-sm text-muted-foreground">Preparando pagamento…</p>
        </div>
      </div>
    );
  }

  if (error || !order || !clientSecret) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center space-y-4 max-w-sm">
          <p className="text-red-600 font-medium">Erro ao iniciar checkout</p>
          <p className="text-sm text-muted-foreground">{error}</p>
          <Button variant="outline" onClick={() => navigate('/')}>
            Voltar para o catálogo
          </Button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <header className="bg-white border-b px-6 py-4">
        <div className="max-w-2xl mx-auto flex items-center gap-3">
          <button
            onClick={() => navigate('/')}
            className="text-sm text-muted-foreground hover:text-foreground"
          >
            ← Voltar
          </button>
          <h1 className="text-xl font-bold">Checkout</h1>
        </div>
      </header>

      <main className="max-w-2xl mx-auto px-4 py-8">
        <Elements
          stripe={stripePromise}
          options={{ clientSecret, locale: 'pt-BR' }}
        >
          <CheckoutForm order={order} />
        </Elements>
      </main>
    </div>
  );
}
