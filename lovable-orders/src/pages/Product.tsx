import { useParams, useNavigate } from "react-router-dom";
import { ArrowLeft, ImageOff, ShoppingCart, Tag, Building2, Minus, Plus, MapPin, Truck, Heart } from "lucide-react";
import Header from "@/components/Header";
import { useProduct } from "@/hooks/useCatalog";
import { Skeleton } from "@/components/ui/skeleton";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { useState } from "react";
import { useCart } from "@/contexts/CartContext";
import { useFavorites } from "@/contexts/FavoritesContext";
import { useToast } from "@/hooks/use-toast";

interface ViaCepResult {
  localidade: string;
  bairro: string;
  logradouro: string;
  uf: string;
  erro?: boolean;
}

interface DeliveryOption {
  name: string;
  days: number;
  price: number; // in BRL cents
}

// Origin: Bela Vista, São Paulo/SP – CEP 01310-100
const ORIGIN_UF = "SP";
const ORIGIN_CITY = "São Paulo";

// Correios PAC/SEDEX estimated business days from SP capital, by destination UF.
// Based on official Correios delivery time tables (SRP – Tabela de Prazo de Entrega).
const DELIVERY_TABLE: Record<string, { pac: number; sedex: number }> = {
  SP: { pac: 1, sedex: 1 },
  RJ: { pac: 3, sedex: 1 },
  MG: { pac: 3, sedex: 1 },
  ES: { pac: 4, sedex: 2 },
  PR: { pac: 3, sedex: 1 },
  SC: { pac: 4, sedex: 2 },
  RS: { pac: 5, sedex: 2 },
  MS: { pac: 4, sedex: 2 },
  MT: { pac: 5, sedex: 2 },
  GO: { pac: 4, sedex: 2 },
  DF: { pac: 4, sedex: 2 },
  BA: { pac: 5, sedex: 3 },
  SE: { pac: 6, sedex: 3 },
  AL: { pac: 6, sedex: 3 },
  PE: { pac: 6, sedex: 3 },
  PB: { pac: 7, sedex: 3 },
  RN: { pac: 7, sedex: 3 },
  CE: { pac: 7, sedex: 3 },
  PI: { pac: 8, sedex: 4 },
  MA: { pac: 8, sedex: 4 },
  PA: { pac: 9, sedex: 5 },
  AP: { pac: 10, sedex: 6 },
  AM: { pac: 10, sedex: 6 },
  RR: { pac: 12, sedex: 7 },
  AC: { pac: 12, sedex: 7 },
  RO: { pac: 10, sedex: 6 },
  TO: { pac: 8, sedex: 4 },
};

function calcDeliveryOptions(destUf: string, priceCents: number): DeliveryOption[] {
  const times = DELIVERY_TABLE[destUf] ?? { pac: 10, sedex: 7 };
  const isFreeShipping = priceCents >= 10000;
  // Simple weight-based estimate: flat R$ 15–35 PAC, R$ 25–55 SEDEX from SP
  const pacPrice = isFreeShipping ? 0 : 1500 + (times.pac - 1) * 200;
  const sedexPrice = 2500 + (times.sedex - 1) * 300;
  return [
    { name: "PAC (Correios)", days: times.pac, price: pacPrice },
    { name: "SEDEX (Correios)", days: times.sedex, price: sedexPrice },
  ];
}

const Product = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { data, isLoading, isError } = useProduct(id ?? "");
  const [selectedImage, setSelectedImage] = useState(0);
  const [quantity, setQuantity] = useState(1);
  const [cep, setCep] = useState("");
  const [cepAddr, setCepAddr] = useState<{ localidade: string; uf: string } | null>(null);
  const [deliveryOptions, setDeliveryOptions] = useState<DeliveryOption[] | null>(null);
  const [cepError, setCepError] = useState<string | null>(null);
  const [cepLoading, setCepLoading] = useState(false);
  const { addItem } = useCart();
  const { isFavorite, toggleFavorite } = useFavorites();
  const { toast } = useToast();

  const product = data?.data;

  const handleAddToCart = () => {
    if (!product) return;
    const priceAmount = product.promotion?.discountedAmount ?? product.price.amount;
    addItem(
      { productId: product.id, name: product.name, priceAmount, images: product.images },
      quantity
    );
    toast({ title: `${quantity}x adicionado ao carrinho`, description: product.name });
  };

  const handleCepLookup = async () => {
    const cleaned = cep.replace(/\D/g, "");
    if (cleaned.length !== 8) {
      setCepError("CEP deve ter 8 dígitos.");
      return;
    }
    setCepLoading(true);
    setCepError(null);
    setCepAddr(null);
    setDeliveryOptions(null);
    try {
      const res = await fetch(`https://viacep.com.br/ws/${cleaned}/json/`);
      const json: ViaCepResult = await res.json();
      if (json.erro) {
        setCepError("CEP não encontrado. Verifique e tente novamente.");
      } else {
        setCepAddr({ localidade: json.localidade, uf: json.uf });
        setDeliveryOptions(calcDeliveryOptions(json.uf, product?.price.amount ?? 0));
      }
    } catch {
      setCepError("Não foi possível consultar o CEP. Tente novamente.");
    } finally {
      setCepLoading(false);
    }
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
            <div className="space-y-5">
              <div>
                <h1 className="text-2xl font-bold text-foreground leading-tight mb-3">
                  {product.name}
                </h1>
                {product.promotion ? (
                  <div className="space-y-1">
                    <div className="flex items-center gap-2">
                      <span className="text-3xl font-bold text-price">
                        {formatPrice(product.promotion.discountedAmount)}
                      </span>
                      <span className="bg-success text-success-foreground text-sm font-bold px-2 py-0.5 rounded">
                        -{product.promotion.discountPercentage}%
                      </span>
                    </div>
                    <p className="text-discount text-base">
                      De: {formatPrice(product.promotion.originalAmount)}
                    </p>
                    <p className="text-xs text-muted-foreground">
                      ⏰ Oferta válida até {new Date(product.promotion.endsAt).toLocaleDateString("pt-BR")}
                    </p>
                  </div>
                ) : (
                  <p className="text-3xl font-bold text-price">
                    {formatPrice(product.price.amount)}
                  </p>
                )}
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

              {/* Description */}
              {product.description && (
                <div className="border-t border-border pt-4">
                  <h2 className="text-sm font-semibold text-foreground mb-2">Descrição</h2>
                  <p className="text-sm text-muted-foreground leading-relaxed whitespace-pre-line">
                    {product.description}
                  </p>
                </div>
              )}

              {/* Quantity selector */}
              <div className="border-t border-border pt-4">
                <h2 className="text-sm font-semibold text-foreground mb-3">Quantidade</h2>
                <div className="flex items-center gap-3">
                  <button
                    onClick={() => setQuantity((q) => Math.max(1, q - 1))}
                    className="w-9 h-9 flex items-center justify-center rounded-lg border border-border hover:bg-accent hover:text-accent-foreground transition-colors"
                  >
                    <Minus className="w-4 h-4" />
                  </button>
                  <span className="w-10 text-center font-semibold text-foreground">{quantity}</span>
                  <button
                    onClick={() => setQuantity((q) => q + 1)}
                    className="w-9 h-9 flex items-center justify-center rounded-lg border border-border hover:bg-accent hover:text-accent-foreground transition-colors"
                  >
                    <Plus className="w-4 h-4" />
                  </button>
                </div>
              </div>

              {/* Description */}
              {product.description && (
                <div className="border-t border-border pt-4">
                  <h2 className="text-sm font-semibold text-foreground mb-2">Descrição</h2>
                  <p className="text-sm text-muted-foreground leading-relaxed whitespace-pre-line">
                    {product.description}
                  </p>
                </div>
              )}

              {/* Quantity selector */}
              <div className="border-t border-border pt-4">
                <h2 className="text-sm font-semibold text-foreground mb-3">Quantidade</h2>
                <div className="flex items-center gap-3">
                  <button
                    onClick={() => setQuantity((q) => Math.max(1, q - 1))}
                    className="w-9 h-9 flex items-center justify-center rounded-lg border border-border hover:bg-accent hover:text-accent-foreground transition-colors"
                  >
                    <Minus className="w-4 h-4" />
                  </button>
                  <span className="w-10 text-center font-semibold text-foreground">{quantity}</span>
                  <button
                    onClick={() => setQuantity((q) => q + 1)}
                    className="w-9 h-9 flex items-center justify-center rounded-lg border border-border hover:bg-accent hover:text-accent-foreground transition-colors"
                  >
                    <Plus className="w-4 h-4" />
                  </button>
                </div>
              </div>

              <div className="flex gap-2">
                <Button
                  className="flex-1 gap-2 bg-brand hover:bg-brand-hover text-primary-foreground"
                  size="lg"
                  onClick={handleAddToCart}
                >
                  <ShoppingCart className="w-5 h-5" />
                  Adicionar ao carrinho
                </Button>
                <Button
                  variant="outline"
                  size="lg"
                  className={`px-4 ${isFavorite(product.id) ? "border-destructive text-destructive" : ""}`}
                  onClick={() =>
                    toggleFavorite({
                      productId: product.id,
                      name: product.name,
                      priceAmount: product.price.amount,
                      images: product.images,
                    })
                  }
                  title={isFavorite(product.id) ? "Remover dos favoritos" : "Favoritar"}
                >
                  <Heart
                    className={`w-5 h-5 ${isFavorite(product.id) ? "fill-destructive text-destructive" : ""}`}
                  />
                </Button>
              </div>

              {/* CEP delivery estimator */}
              <div className="border border-border rounded-xl p-4 space-y-3">
                <div className="flex items-center gap-2 text-sm font-semibold text-foreground">
                  <Truck className="w-4 h-4 text-brand" />
                  Calcular entrega
                </div>
                <p className="text-xs text-muted-foreground -mt-1">
                  Envio a partir de {ORIGIN_CITY}/{ORIGIN_UF} • Prazos Correios (dias úteis)
                </p>
                <div className="flex gap-2">
                  <div className="relative flex-1">
                    <MapPin className="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input
                      placeholder="00000-000"
                      value={cep}
                      onChange={(e) => {
                        const v = e.target.value.replace(/\D/g, "").slice(0, 8);
                        setCep(v.length > 5 ? `${v.slice(0, 5)}-${v.slice(5)}` : v);
                        setCepError(null);
                        setDeliveryOptions(null);
                        setCepAddr(null);
                      }}
                      onKeyDown={(e) => e.key === "Enter" && handleCepLookup()}
                      className="pl-8"
                      maxLength={9}
                    />
                  </div>
                  <Button
                    variant="outline"
                    onClick={handleCepLookup}
                    disabled={cepLoading}
                    className="shrink-0"
                  >
                    {cepLoading ? "..." : "Calcular"}
                  </Button>
                </div>

                {cepError && (
                  <p className="text-sm text-destructive">{cepError}</p>
                )}

                {cepAddr && deliveryOptions && (
                  <div className="space-y-2 pt-1">
                    <p className="text-xs text-muted-foreground">
                      📍 {cepAddr.localidade}/{cepAddr.uf}
                    </p>
                    {deliveryOptions.map((opt) => (
                      <div
                        key={opt.name}
                        className="flex items-center justify-between rounded-lg border border-border bg-secondary/40 px-3 py-2 text-sm"
                      >
                        <div>
                          <span className="font-medium text-foreground">{opt.name}</span>
                          <span className="ml-2 text-muted-foreground">
                            até {opt.days} dia{opt.days > 1 ? "s" : ""} útil{opt.days > 1 ? "eis" : ""}
                          </span>
                        </div>
                        <span className={opt.price === 0 ? "text-[hsl(var(--success))] font-semibold" : "font-semibold text-foreground"}>
                          {opt.price === 0
                            ? "Grátis"
                            : (opt.price / 100).toLocaleString("pt-BR", { style: "currency", currency: "BRL" })}
                        </span>
                      </div>
                    ))}
                    <p className="text-[10px] text-muted-foreground pt-1">
                      * Prazos estimados conforme tabela oficial dos Correios. Consulta via ViaCEP.
                    </p>
                  </div>
                )}
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default Product;
