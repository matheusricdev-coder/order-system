import { useParams, useNavigate } from "react-router-dom";
import { ArrowLeft, ImageOff, ShoppingCart, Tag, Building2 } from "lucide-react";
import Header from "@/components/Header";
import { useProduct } from "@/hooks/useCatalog";
import { Skeleton } from "@/components/ui/skeleton";
import { Button } from "@/components/ui/button";
import { useState } from "react";
import { useCart } from "@/contexts/CartContext";
import { useToast } from "@/hooks/use-toast";

const Product = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { data, isLoading, isError } = useProduct(id ?? "");
  const [selectedImage, setSelectedImage] = useState(0);
  const { addItem } = useCart();
  const { toast } = useToast();

  const product = data?.data;

  const handleAddToCart = () => {
    if (!product) return;
    addItem({
      productId: product.id,
      name: product.name,
      priceAmount: product.price.amount,
      images: product.images,
    });
    toast({ title: "Adicionado ao carrinho", description: product.name });
  };

  const formatPrice = (amount: number) =>
    (amount / 100).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });

  return (
    <div className="min-h-screen bg-background">
      <Header />

      <div className="container py-4 max-w-4xl">
        {/* Back */}
        <button
          onClick={() => navigate(-1)}
          className="flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors mb-6"
        >
          <ArrowLeft className="w-4 h-4" />
          Voltar
        </button>

        {isLoading && (
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <Skeleton className="aspect-square rounded-xl" />
            <div className="space-y-4">
              <Skeleton className="h-8 w-3/4" />
              <Skeleton className="h-6 w-1/3" />
              <Skeleton className="h-12 w-full" />
            </div>
          </div>
        )}

        {isError && (
          <div className="text-center py-24 text-muted-foreground">
            <p className="text-lg font-medium">Produto não encontrado.</p>
            <button onClick={() => navigate("/")} className="mt-4 text-brand underline text-sm">
              Voltar para o início
            </button>
          </div>
        )}

        {product && (
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8 animate-fade-in">
            {/* Gallery */}
            <div className="space-y-3">
              <div className="aspect-square rounded-xl bg-card overflow-hidden border border-border">
                {product.images.length > 0 ? (
                  <img
                    src={product.images[selectedImage]}
                    alt={product.name}
                    className="w-full h-full object-cover"
                    onError={(e) => {
                      (e.currentTarget as HTMLImageElement).style.display = "none";
                      e.currentTarget.nextElementSibling?.classList.remove("hidden");
                    }}
                  />
                ) : null}
                <div
                  className={`${product.images.length > 0 ? "hidden" : ""} w-full h-full flex flex-col items-center justify-center text-muted-foreground gap-3`}
                >
                  <ImageOff className="w-16 h-16 opacity-30" />
                  <span className="text-sm opacity-60">Sem imagem disponível</span>
                </div>
              </div>

              {/* Thumbnails */}
              {product.images.length > 1 && (
                <div className="flex gap-2 overflow-x-auto no-scrollbar">
                  {product.images.map((url, i) => (
                    <button
                      key={i}
                      onClick={() => setSelectedImage(i)}
                      className={`flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 transition-colors ${
                        selectedImage === i ? "border-brand" : "border-border"
                      }`}
                    >
                      <img src={url} alt={`${product.name} ${i + 1}`} className="w-full h-full object-cover" />
                    </button>
                  ))}
                </div>
              )}
            </div>

            {/* Info */}
            <div className="space-y-6">
              <div>
                <h1 className="text-2xl font-bold text-foreground leading-tight mb-3">
                  {product.name}
                </h1>
                <p className="text-3xl font-bold text-price">
                  {formatPrice(product.price.amount)}
                </p>
                {product.price.amount >= 10000 && (
                  <p className="badge-free-shipping mt-1">Frete grátis</p>
                )}
              </div>

              <div className="space-y-2 text-sm text-muted-foreground">
                {product.categoryName && (
                  <div className="flex items-center gap-2">
                    <Tag className="w-4 h-4" />
                    <span>{product.categoryName}</span>
                  </div>
                )}
                {product.companyName && (
                  <div className="flex items-center gap-2">
                    <Building2 className="w-4 h-4" />
                    <span>Vendido por: {product.companyName}</span>
                  </div>
                )}
              </div>

              <Button
                className="w-full gap-2 bg-brand hover:bg-brand-hover text-primary-foreground"
                size="lg"
                onClick={handleAddToCart}
              >
                <ShoppingCart className="w-5 h-5" />
                Adicionar ao carrinho
              </Button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default Product;
