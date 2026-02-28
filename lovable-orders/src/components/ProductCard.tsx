import { Heart, ImageOff } from "lucide-react";
import { useNavigate } from "react-router-dom";

const DEFAULT_IMAGE = "/placeholder-product.svg";

interface ProductCardProps {
  id: string;
  title: string;
  price: number;
  originalPrice?: number;
  images?: string[];
  freeShipping?: boolean;
}

const ProductCard = ({ id, title, price, originalPrice, images = [], freeShipping }: ProductCardProps) => {
  const navigate = useNavigate();
  const coverImage = images.length > 0 ? images[0] : null;
  const discount = originalPrice 
    ? Math.round(((originalPrice - price) / originalPrice) * 100) 
    : 0;

  const formatPrice = (value: number) => {
    return value.toLocaleString('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    });
  };

  return (
    <div className="product-card cursor-pointer group" onClick={() => navigate(`/products/${id}`)}>
      <div className="relative aspect-square bg-card overflow-hidden">
        {coverImage ? (
          <img
            src={coverImage}
            alt={title}
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            onError={(e) => {
              (e.currentTarget as HTMLImageElement).style.display = 'none';
              e.currentTarget.nextElementSibling?.classList.remove('hidden');
            }}
          />
        ) : null}
        <div className={`${coverImage ? 'hidden' : ''} w-full h-full flex flex-col items-center justify-center bg-muted text-muted-foreground gap-2`}>
          <ImageOff className="w-10 h-10 opacity-40" />
          <span className="text-xs opacity-60">Sem imagem</span>
        </div>
        <button 
          className="absolute top-2 right-2 p-1.5 bg-card/80 backdrop-blur-sm rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
          onClick={(e) => {
            e.stopPropagation();
          }}
        >
          <Heart className="w-4 h-4 text-muted-foreground hover:text-destructive transition-colors" />
        </button>
        {discount > 0 && (
          <span className="absolute top-2 left-2 bg-success text-success-foreground text-xs font-semibold px-2 py-0.5 rounded">
            {discount}% OFF
          </span>
        )}
      </div>
      <div className="p-3">
        <h3 className="text-sm text-foreground line-clamp-2 mb-2 leading-tight">
          {title}
        </h3>
        <div className="space-y-0.5">
          {originalPrice && (
            <span className="text-discount">
              {formatPrice(originalPrice)}
            </span>
          )}
          <p className="text-price text-lg">
            {formatPrice(price)}
          </p>
          {freeShipping && (
            <p className="badge-free-shipping">Frete grátis</p>
          )}
        </div>
      </div>
    </div>
  );
};

export default ProductCard;
