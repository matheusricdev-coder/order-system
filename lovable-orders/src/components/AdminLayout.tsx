import { Link, useLocation } from "react-router-dom";
import { LayoutDashboard, Package, ShoppingBag, Boxes } from "lucide-react";

const NAV = [
  { to: "/admin/products", label: "Produtos",  icon: Package      },
  { to: "/admin/stocks",   label: "Estoque",   icon: Boxes        },
  { to: "/admin/orders",   label: "Pedidos",   icon: ShoppingBag  },
];

const AdminLayout = ({ children }: { children: React.ReactNode }) => {
  const { pathname } = useLocation();

  return (
    <div className="min-h-screen bg-background flex">
      {/* Sidebar */}
      <aside className="w-56 shrink-0 border-r border-border bg-card flex flex-col">
        <div className="p-4 flex items-center gap-2 border-b border-border">
          <LayoutDashboard className="h-5 w-5 text-brand" />
          <span className="font-bold text-foreground">Admin</span>
        </div>
        <nav className="flex-1 p-2 space-y-1">
          {NAV.map(({ to, label, icon: Icon }) => (
            <Link
              key={to}
              to={to}
              className={`flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors ${
                pathname.startsWith(to)
                  ? "bg-brand text-primary-foreground"
                  : "text-foreground hover:bg-secondary"
              }`}
            >
              <Icon className="h-4 w-4" />
              {label}
            </Link>
          ))}
        </nav>
        <div className="p-4 border-t border-border">
          <Link to="/" className="text-xs text-muted-foreground hover:text-foreground">
            ← Voltar ao site
          </Link>
        </div>
      </aside>

      {/* Content */}
      <main className="flex-1 overflow-auto p-6">{children}</main>
    </div>
  );
};

export default AdminLayout;
