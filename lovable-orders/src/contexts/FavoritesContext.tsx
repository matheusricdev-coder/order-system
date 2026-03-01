import { createContext, useContext, useState, useEffect, useCallback, ReactNode } from "react";

export interface FavoriteItem {
  productId: string;
  slug: string;
  name: string;
  priceAmount: number; // in cents
  images: string[];
}

interface FavoritesContextValue {
  items: FavoriteItem[];
  isFavorite: (productId: string) => boolean;
  toggleFavorite: (item: FavoriteItem) => void;
  removeFavorite: (productId: string) => void;
  clearFavorites: () => void;
}

const FavoritesContext = createContext<FavoritesContextValue | undefined>(undefined);

const STORAGE_KEY = "favorites_items";

export function FavoritesProvider({ children }: { children: ReactNode }) {
  const [items, setItems] = useState<FavoriteItem[]>(() => {
    try {
      const stored = localStorage.getItem(STORAGE_KEY);
      return stored ? (JSON.parse(stored) as FavoriteItem[]) : [];
    } catch {
      return [];
    }
  });

  useEffect(() => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
  }, [items]);

  const isFavorite = useCallback(
    (productId: string) => items.some((i) => i.productId === productId),
    [items]
  );

  const toggleFavorite = useCallback((item: FavoriteItem) => {
    setItems((prev) => {
      const exists = prev.find((i) => i.productId === item.productId);
      return exists ? prev.filter((i) => i.productId !== item.productId) : [...prev, item];
    });
  }, []);

  const removeFavorite = useCallback((productId: string) => {
    setItems((prev) => prev.filter((i) => i.productId !== productId));
  }, []);

  const clearFavorites = useCallback(() => setItems([]), []);

  return (
    <FavoritesContext.Provider value={{ items, isFavorite, toggleFavorite, removeFavorite, clearFavorites }}>
      {children}
    </FavoritesContext.Provider>
  );
}

export function useFavorites() {
  const ctx = useContext(FavoritesContext);
  if (!ctx) throw new Error("useFavorites must be used within FavoritesProvider");
  return ctx;
}
