import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { AuthProvider } from "@/contexts/AuthContext";
import { CartProvider } from "@/contexts/CartContext";
import { FavoritesProvider } from "@/contexts/FavoritesContext";
import CartDrawer from "@/components/CartDrawer";
import ProtectedRoute from "@/components/ProtectedRoute";
import AdminRoute from "@/components/AdminRoute";
import Index from "./pages/Index";
import Login from "./pages/Login";
import Product from "./pages/Product";
import Checkout from "./pages/Checkout";
import Orders from "./pages/Orders";
import OrderDetail from "./pages/OrderDetail";
import Favorites from "./pages/Favorites";
import NotFound from "./pages/NotFound";
import AdminProducts from "./pages/admin/AdminProducts";
import AdminStock from "./pages/admin/AdminStock";
import AdminOrders from "./pages/admin/AdminOrders";

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});

const App = () => (
  <QueryClientProvider client={queryClient}>
    <AuthProvider>
      <CartProvider>
        <FavoritesProvider>
          <TooltipProvider>
          <Toaster />
          <Sonner />
          <BrowserRouter>
            <CartDrawer />
            <Routes>
              {/* ── Public ─────────────────────────────────────────────── */}
              <Route path="/" element={<Index />} />
              <Route path="/login" element={<Login />} />
              <Route path="/products/:slug" element={<Product />} />
              <Route path="/favorites" element={<Favorites />} />

              {/* ── Authenticated ───────────────────────────────────────── */}
              <Route
                path="/checkout"
                element={<ProtectedRoute><Checkout /></ProtectedRoute>}
              />
              <Route
                path="/orders"
                element={<ProtectedRoute><Orders /></ProtectedRoute>}
              />
              <Route
                path="/orders/:orderNumber"
                element={<ProtectedRoute><OrderDetail /></ProtectedRoute>}
              />

              {/* ── Admin (role=admin required) ─────────────────────────── */}
              <Route
                path="/admin/products"
                element={<AdminRoute><AdminProducts /></AdminRoute>}
              />
              <Route
                path="/admin/stocks"
                element={<AdminRoute><AdminStock /></AdminRoute>}
              />
              <Route
                path="/admin/orders"
                element={<AdminRoute><AdminOrders /></AdminRoute>}
              />

              {/* ── Catch-all ───────────────────────────────────────────── */}
              <Route path="*" element={<NotFound />} />
            </Routes>
          </BrowserRouter>
          </TooltipProvider>
        </FavoritesProvider>
      </CartProvider>
    </AuthProvider>
  </QueryClientProvider>
);

export default App;

