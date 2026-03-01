import { useNavigate } from "react-router-dom";
import { Heart, Trash2, ShoppingCart, ImageOff } from "lucide-react";
import Header from "@/components/Header";
import { useFavorites } from "@/contexts/FavoritesContext";
import { useCart } from "@/contexts/CartContext";
import { Button } from "@/components/ui/button";
import { useToast } from "@/hooks/use-toast";

const Favorites = () => {
  const navigate = useNavigate();
  const { items, removeFavorite, clearFavorites } = useFavorites();
  const { addItem } = useCart();
  const { toast } = useToast();

  const formatPrice = (cents: number) =>
    (cents / 100).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });

  const handleAddToCart = (item: (typeof items)[number]) => {
    addItem({ productId: item.productId, name: item.name, priceAmount: item.priceAmount, images: item.images });
    toast({ title: "Adicionado ao carrinho", description: item.name });
  };

  return (
    <div className="min-h-screen bg-background">
      <Header />

      <div className="container py-6 max-w-4xl">
        <div className="flex items-center justify-between mb-6">
          <div className="flex items-center gap-2">
            <Heart className="w-5 h-5 text-destructive fill-destructive" />
            <h1 className="text-xl font-bold text-foreground">Meus Favoritos</h1>
            {items.length > 0 && (
              <span className="text-sm text-muted-foreground">({items.length})</span>
            )}
          </div>
          {items.length > 0 && (
            <Button variant="ghost" size="sm" className="text-muted-foreground" onClick={clearFavorites}>
              <Trash2 className="w-4 h-4 mr-1.5" />
              Limpar tudo
            </Button>
          )}
        </div>

        {items.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-24 text-muted-foreground gap-4">
            <Heart className="w-16 h-16 opacity-20" />
            <p className="text-lg font-medium">Nenhum favorito ainda</p>
            <p className="text-sm opacity-70">Explore os produtos e clique no ❤️ para salvar.</p>
            <Button className="mt-2 bg-brand hover:bg-brand-hover text-primary-foreground" onClick={() => navigate("/")}>
              Explorar produtos
            </Button>
          </div>
        ) : (
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {items.map((item) => {
              const cover = item.images[0] ?? null;
              return (
                <div
                  key={item.productId}
                  className="product-card group animate-fade-in"
                >
                  <div
                    className="relative aspect-square bg-card overflow-hidden cursor-pointer"
                    onClick={() => navigate(`/products/${item.slug}`)}
                  >
                    {cover ? (
                      <img
                        src={cover}
                        alt={item.name}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                      />
                    ) : (
                      <div className="w-full h-full flex flex-col items-center justify-center bg-muted text-muted-foreground gap-2">
                        <ImageOff className="w-10 h-10 opacity-40" />
                        <span className="text-xs opacity-60">Sem imagem</span>
                      </div>
                    )}
                    <button
                      className="absolute top-2 right-2 p-1.5 bg-card/80 backdrop-blur-sm rounded-full"
                      onClick={(e) => {
                        e.stopPropagation();
                        removeFavorite(item.productId);
                      }}
                      title="Remover dos favoritos"
                    >
                      <Heart className="w-4 h-4 fill-destructive text-destructive" />
                    </button>
                  </div>
                  <div className="p-3 space-y-2">
                    <p
                      className="text-sm text-foreground line-clamp-2 leading-tight cursor-pointer hover:underline"
                      onClick={() => navigate(`/products/${item.slug || item.productId}`)}
                    >
                      {item.name}
                    </p>
                    <p className="text-price text-base">{formatPrice(item.priceAmount)}</p>
                    <Button
                      size="sm"
                      className="w-full gap-1.5 bg-brand hover:bg-brand-hover text-primary-foreground"
                      onClick={() => handleAddToCart(item)}
                    >
                      <ShoppingCart className="w-3.5 h-3.5" />
                      Adicionar
                    </Button>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
};

export default Favorites;
