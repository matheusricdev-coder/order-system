import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { Link } from "react-router-dom";
import { adminApi } from "@/lib/api";
import AdminLayout from "@/components/AdminLayout";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import type { OrderStatus } from "@/types/api";

const fmt = (cents: number) =>
  new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" }).format(
    cents / 100,
  );

const STATUS_CONFIG: Record<
  OrderStatus,
  { label: string; variant: "default" | "secondary" | "destructive" | "outline" }
> = {
  created:         { label: "Criado",    variant: "outline"     },
  payment_pending: { label: "Aguard. Pag.", variant: "secondary" },
  paid:            { label: "Pago",      variant: "default"     },
  cancelled:       { label: "Cancelado", variant: "destructive" },
};

const STATUS_FILTERS = ["", "created", "payment_pending", "paid", "cancelled"];

const AdminOrders = () => {
  const [statusFilter, setStatusFilter] = useState<string>("");
  const [page, setPage] = useState(1);

  const { data, isLoading } = useQuery({
    queryKey: ["admin-orders", statusFilter, page],
    queryFn: () =>
      adminApi.listOrders({
        status: statusFilter || undefined,
        page,
        perPage: 25,
      }),
  });

  const orders = data?.data ?? [];
  const meta   = data?.meta;

  return (
    <AdminLayout>
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-2xl font-bold">Pedidos</h1>

        <Select
          value={statusFilter}
          onValueChange={(v) => { setStatusFilter(v === "all" ? "" : v); setPage(1); }}
        >
          <SelectTrigger className="w-44">
            <SelectValue placeholder="Todos os status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">Todos os status</SelectItem>
            <SelectItem value="created">Criado</SelectItem>
            <SelectItem value="payment_pending">Aguard. pagamento</SelectItem>
            <SelectItem value="paid">Pago</SelectItem>
            <SelectItem value="cancelled">Cancelado</SelectItem>
          </SelectContent>
        </Select>
      </div>

      {isLoading ? (
        <p className="text-muted-foreground">Carregando…</p>
      ) : (
        <>
          <div className="rounded-xl border border-border overflow-hidden">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>ID</TableHead>
                  <TableHead>Usuário</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead className="text-right">Total</TableHead>
                  <TableHead>Criado em</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {orders.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={5} className="text-center text-muted-foreground py-8">
                      Nenhum pedido encontrado.
                    </TableCell>
                  </TableRow>
                ) : (
                  orders.map((o) => {
                    const cfg = STATUS_CONFIG[o.status as OrderStatus];
                    return (
                      <TableRow key={o.id}>
                        <TableCell>
                          <Link
                            to={`/admin/orders/${o.id}`}
                            className="font-mono text-xs text-brand hover:underline"
                          >
                            {o.id.substring(0, 8)}…
                          </Link>
                        </TableCell>
                        <TableCell className="font-mono text-xs text-muted-foreground">
                          {o.userId?.substring(0, 8)}…
                        </TableCell>
                        <TableCell>
                          <Badge variant={cfg?.variant ?? "outline"}>
                            {cfg?.label ?? o.status}
                          </Badge>
                        </TableCell>
                        <TableCell className="text-right font-mono text-sm">
                          {fmt(o.total?.amount ?? 0)}
                        </TableCell>
                        <TableCell className="text-sm text-muted-foreground">
                          {o.createdAt
                            ? new Date(o.createdAt).toLocaleString("pt-BR")
                            : "—"}
                        </TableCell>
                      </TableRow>
                    );
                  })
                )}
              </TableBody>
            </Table>
          </div>

          {/* Pagination */}
          {meta && meta.lastPage > 1 && (
            <div className="flex justify-center gap-2 mt-4">
              <Button variant="outline" size="sm" disabled={page === 1} onClick={() => setPage(page - 1)}>
                Anterior
              </Button>
              <span className="text-sm text-muted-foreground self-center">
                {page} / {meta.lastPage}
              </span>
              <Button variant="outline" size="sm" disabled={page === meta.lastPage} onClick={() => setPage(page + 1)}>
                Próxima
              </Button>
            </div>
          )}
        </>
      )}
    </AdminLayout>
  );
};

export default AdminOrders;
