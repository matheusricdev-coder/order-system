import { useState } from "react";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { adminApi, type AdminStock } from "@/lib/api";
import AdminLayout from "@/components/AdminLayout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { Save } from "lucide-react";

const AdminStock = () => {
  const qc = useQueryClient();
  const { data, isLoading } = useQuery({
    queryKey: ["admin-stocks"],
    queryFn: () => adminApi.listStocks(),
  });

  // Local edit state: productId → edited total string
  const [edits, setEdits] = useState<Record<string, string>>({});
  const [savingId, setSavingId] = useState<string | null>(null);

  const updateMut = useMutation({
    mutationFn: ({ productId, qty }: { productId: string; qty: number }) =>
      adminApi.updateStock(productId, qty),
    onSuccess: (_, { productId }) => {
      setSavingId(null);
      setEdits((e) => { const next = { ...e }; delete next[productId]; return next; });
      qc.invalidateQueries({ queryKey: ["admin-stocks"] });
    },
    onError: () => setSavingId(null),
  });

  const handleSave = (productId: string) => {
    const qty = parseInt(edits[productId] ?? "", 10);
    if (isNaN(qty) || qty < 0) return;
    setSavingId(productId);
    updateMut.mutate({ productId, qty });
  };

  const stocks: AdminStock[] = data?.data ?? [];

  return (
    <AdminLayout>
      <h1 className="text-2xl font-bold mb-6">Estoque</h1>

      {isLoading ? (
        <p className="text-muted-foreground">Carregando…</p>
      ) : (
        <div className="rounded-xl border border-border overflow-hidden">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Produto ID</TableHead>
                <TableHead className="text-center">Reservado</TableHead>
                <TableHead className="text-center">Disponível</TableHead>
                <TableHead className="text-center">Total</TableHead>
                <TableHead />
              </TableRow>
            </TableHeader>
            <TableBody>
              {stocks.map((s) => {
                const low = s.quantityAvailable <= 5;
                const editValue = edits[s.productId] ?? String(s.quantityTotal);
                const dirty = edits[s.productId] !== undefined;
                return (
                  <TableRow key={s.productId}>
                    <TableCell className="font-mono text-xs text-muted-foreground">
                      {s.productId}
                    </TableCell>
                    <TableCell className="text-center">{s.quantityReserved}</TableCell>
                    <TableCell className="text-center">
                      <Badge variant={low ? "destructive" : "secondary"}>
                        {s.quantityAvailable}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-center">
                      <Input
                        type="number"
                        min="0"
                        value={editValue}
                        onChange={(e) =>
                          setEdits((prev) => ({ ...prev, [s.productId]: e.target.value }))
                        }
                        className="w-24 mx-auto text-center"
                      />
                    </TableCell>
                    <TableCell className="text-right">
                      <Button
                        size="sm"
                        variant={dirty ? "default" : "ghost"}
                        disabled={!dirty || savingId === s.productId}
                        onClick={() => handleSave(s.productId)}
                        className="gap-1"
                      >
                        <Save className="h-3 w-3" />
                        {savingId === s.productId ? "…" : "Salvar"}
                      </Button>
                    </TableCell>
                  </TableRow>
                );
              })}
            </TableBody>
          </Table>
        </div>
      )}
    </AdminLayout>
  );
};

export default AdminStock;
