import { X, Minus, Plus, Trash2, ShoppingBag, ShoppingCart } from "lucide-react";
import { useCart } from "@/contexts/CartContext";
import { useAuth } from "@/contexts/AuthContext";
import { useNavigate } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { ImageOff } from "lucide-react";

const CartDrawer = () => {
  const { items, isOpen, closeCart, removeItem, updateQuantity, clearCart, totalAmount } = useCart();
  const { isAuthenticated } = useAuth();
  const navigate = useNavigate();

  const formatPrice = (cents: number) =>
    (cents / 100).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });

  const handleCheckout = () => {
    if (items.length === 0) return;

    if (!isAuthenticated) {
      closeCart();
      navigate("/login");
      return;
    }

    closeCart();
    navigate("/checkout");
  };

  return (
    <>
      {/* Backdrop */}
      {isOpen && (
        <div
          className="fixed inset-0 bg-black/50 z-40 backdrop-blur-sm"
          onClick={closeCart}
        />
      )}

      {/* Drawer */}
      <div
        className={`fixed top-0 right-0 h-full w-full sm:w-[420px] bg-background z-50 shadow-2xl flex flex-col transition-transform duration-300 ${
          isOpen ? "translate-x-0" : "translate-x-full"
        }`}
      >
        {/* Header */}
        <div className="flex items-center justify-between p-4 border-b border-border">
          <div className="flex items-center gap-2">
            <ShoppingCart className="w-5 h-5 text-brand" />
            <h2 className="text-lg font-bold text-foreground">Carrinho</h2>
            {items.length > 0 && (
              <span className="text-sm text-muted-foreground">({items.length} {items.length === 1 ? "item" : "itens"})</span>
            )}
          </div>
          <button
            onClick={closeCart}
            className="p-2 hover:bg-secondary rounded-lg transition-colors"
          >
            <X className="w-5 h-5 text-muted-foreground" />
          </button>
        </div>

        {/* Items */}
        <div className="flex-1 overflow-y-auto p-4 space-y-4">
          {items.length === 0 ? (
            <div className="flex flex-col items-center justify-center h-full text-muted-foreground gap-4">
              <ShoppingBag className="w-16 h-16 opacity-20" />
              <p className="text-sm">Seu carrinho está vazio.</p>
            </div>
          ) : (
            items.map((item) => (
              <div key={item.productId} className="flex gap-3 bg-card rounded-xl p-3 border border-border">
                {/* Thumbnail */}
                <div className="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden bg-muted">
                  {item.images.length > 0 ? (
                    <img
                      src={item.images[0]}
                      alt={item.name}
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    <div className="w-full h-full flex items-center justify-center">
                      <ImageOff className="w-6 h-6 opacity-30 text-muted-foreground" />
                    </div>
                  )}
                </div>

                {/* Info */}
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium text-foreground line-clamp-2 leading-tight">
                    {item.name}
                  </p>
                  <p className="text-sm font-bold text-price mt-1">
                    {formatPrice(item.priceAmount)}
                  </p>

                  {/* Quantity controls */}
                  <div className="flex items-center gap-2 mt-2">
                    <button
                      onClick={() => updateQuantity(item.productId, item.quantity - 1)}
                      className="w-7 h-7 flex items-center justify-center rounded-full border border-border hover:bg-secondary transition-colors"
                    >
                      <Minus className="w-3 h-3" />
                    </button>
                    <span className="w-6 text-center text-sm font-semibold">{item.quantity}</span>
                    <button
                      onClick={() => updateQuantity(item.productId, item.quantity + 1)}
                      className="w-7 h-7 flex items-center justify-center rounded-full border border-border hover:bg-secondary transition-colors"
                    >
                      <Plus className="w-3 h-3" />
                    </button>
                  </div>
                </div>

                {/* Remove */}
                <button
                  onClick={() => removeItem(item.productId)}
                  className="p-1.5 self-start hover:text-destructive transition-colors text-muted-foreground"
                >
                  <Trash2 className="w-4 h-4" />
                </button>
              </div>
            ))
          )}
        </div>

        {/* Footer */}
        {items.length > 0 && (
          <div className="p-4 border-t border-border space-y-3">
            <div className="flex items-center justify-between">
              <span className="text-sm text-muted-foreground">Total</span>
              <span className="text-xl font-bold text-foreground">{formatPrice(totalAmount)}</span>
            </div>
            <Button
              className="w-full bg-brand hover:bg-brand-hover text-primary-foreground"
              size="lg"
              onClick={handleCheckout}
            >
              Ir para o checkout
            </Button>
            <button
              onClick={clearCart}
              className="w-full text-xs text-muted-foreground hover:text-destructive transition-colors text-center"
            >
              Limpar carrinho
            </button>
          </div>
        )}
      </div>
    </>
  );
};

export default CartDrawer;
