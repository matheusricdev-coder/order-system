import { useState } from "react";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { adminApi, type AdminProduct } from "@/lib/api";
import AdminLayout from "@/components/AdminLayout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import {
  Dialog,
  DialogTrigger,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from "@/components/ui/dialog";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Pencil, Trash2, Plus } from "lucide-react";
import { useCategories } from "@/hooks/useCatalog";

// ── Helpers ──────────────────────────────────────────────────────────────────

const fmt = (cents: number) =>
  new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" }).format(
    cents / 100,
  );

// ── Product form (create / edit) ─────────────────────────────────────────────

type ProductFormState = {
  name: string;
  description: string;
  categoryId: string;
  companyId: string;
  priceAmount: string; // BRL display value
};

const EMPTY_FORM: ProductFormState = {
  name: "",
  description: "",
  categoryId: "",
  companyId: "",
  priceAmount: "",
};

function productToForm(p: AdminProduct): ProductFormState {
  return {
    name: p.name,
    description: p.description ?? "",
    categoryId: p.categoryId,
    companyId: p.companyId,
    priceAmount: String(p.price.amount / 100),
  };
}

interface ProductFormProps {
  initial?: ProductFormState;
  onSave: (data: ProductFormState) => void;
  saving: boolean;
  onCancel: () => void;
}

function ProductForm({ initial = EMPTY_FORM, onSave, saving, onCancel }: ProductFormProps) {
  const [form, setForm] = useState<ProductFormState>(initial);
  const { data: categoriesRes } = useCategories();
  const categories = categoriesRes?.data ?? [];

  const set = (key: keyof ProductFormState) => (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>,
  ) => setForm((f) => ({ ...f, [key]: e.target.value }));

  return (
    <div className="space-y-4">
      <div>
        <label className="block text-sm font-medium mb-1">Nome *</label>
        <Input value={form.name} onChange={set("name")} placeholder="Nome do produto" />
      </div>
      <div>
        <label className="block text-sm font-medium mb-1">Descrição</label>
        <textarea
          value={form.description}
          onChange={set("description")}
          rows={3}
          placeholder="Descrição opcional"
          className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none"
        />
      </div>
      <div>
        <label className="block text-sm font-medium mb-1">Categoria *</label>
        <select
          value={form.categoryId}
          onChange={set("categoryId")}
          className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"
        >
          <option value="">Selecione...</option>
          {categories.map((c) => (
            <option key={c.id} value={c.id}>{c.name}</option>
          ))}
        </select>
      </div>
      <div>
        <label className="block text-sm font-medium mb-1">Company ID *</label>
        <Input value={form.companyId} onChange={set("companyId")} placeholder="UUID da empresa" />
      </div>
      <div>
        <label className="block text-sm font-medium mb-1">Preço (R$) *</label>
        <Input
          type="number"
          min="0"
          step="0.01"
          value={form.priceAmount}
          onChange={set("priceAmount")}
          placeholder="0,00"
        />
      </div>
      <DialogFooter>
        <Button variant="outline" onClick={onCancel}>Cancelar</Button>
        <Button onClick={() => onSave(form)} disabled={saving}>
          {saving ? "Salvando…" : "Salvar"}
        </Button>
      </DialogFooter>
    </div>
  );
}

// ── Page ─────────────────────────────────────────────────────────────────────

const AdminProducts = () => {
  const qc = useQueryClient();

  const [page, setPage] = useState(1);
  const { data, isLoading } = useQuery({
    queryKey: ["admin-products", page],
    queryFn: () => adminApi.listProducts({ page, perPage: 20 }),
  });

  const [createOpen, setCreateOpen] = useState(false);
  const [editTarget, setEditTarget] = useState<AdminProduct | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<AdminProduct | null>(null);

  const invalidate = () => qc.invalidateQueries({ queryKey: ["admin-products"] });

  const createMut = useMutation({
    mutationFn: (f: ProductFormState) =>
      adminApi.createProduct({
        name: f.name,
        description: f.description || undefined,
        categoryId: f.categoryId,
        companyId: f.companyId,
        priceAmount: Math.round(parseFloat(f.priceAmount) * 100),
      }),
    onSuccess: () => { setCreateOpen(false); invalidate(); },
  });

  const updateMut = useMutation({
    mutationFn: ({ id, form }: { id: string; form: ProductFormState }) =>
      adminApi.updateProduct(id, {
        name: form.name,
        description: form.description || undefined,
        categoryId: form.categoryId,
        priceAmount: Math.round(parseFloat(form.priceAmount) * 100),
      }),
    onSuccess: () => { setEditTarget(null); invalidate(); },
  });

  const deleteMut = useMutation({
    mutationFn: (id: string) => adminApi.deleteProduct(id),
    onSuccess: () => { setDeleteTarget(null); invalidate(); },
  });

  const products = data?.data ?? [];
  const meta = data?.meta;

  return (
    <AdminLayout>
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-2xl font-bold">Produtos</h1>
        <Dialog open={createOpen} onOpenChange={setCreateOpen}>
          <DialogTrigger asChild>
            <Button className="gap-2"><Plus className="h-4 w-4" /> Novo produto</Button>
          </DialogTrigger>
          <DialogContent className="sm:max-w-lg">
            <DialogHeader><DialogTitle>Criar produto</DialogTitle></DialogHeader>
            <ProductForm
              onSave={(f) => createMut.mutate(f)}
              saving={createMut.isPending}
              onCancel={() => setCreateOpen(false)}
            />
          </DialogContent>
        </Dialog>
      </div>

      {isLoading ? (
        <p className="text-muted-foreground">Carregando…</p>
      ) : (
        <>
          <div className="rounded-xl border border-border overflow-hidden">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Nome</TableHead>
                  <TableHead>Categoria</TableHead>
                  <TableHead>Empresa</TableHead>
                  <TableHead className="text-right">Preço</TableHead>
                  <TableHead />
                </TableRow>
              </TableHeader>
              <TableBody>
                {products.map((p) => (
                  <TableRow key={p.id}>
                    <TableCell className="font-medium">{p.name}</TableCell>
                    <TableCell>
                      <Badge variant="secondary">{p.categoryName ?? p.categoryId}</Badge>
                    </TableCell>
                    <TableCell className="text-sm text-muted-foreground">
                      {p.companyName ?? p.companyId}
                    </TableCell>
                    <TableCell className="text-right font-mono text-sm">
                      {fmt(p.price.amount)}
                    </TableCell>
                    <TableCell className="text-right">
                      <div className="flex justify-end gap-2">
                        <Button
                          size="icon"
                          variant="ghost"
                          onClick={() => setEditTarget(p)}
                        >
                          <Pencil className="h-4 w-4" />
                        </Button>
                        <Button
                          size="icon"
                          variant="ghost"
                          className="text-destructive hover:text-destructive"
                          onClick={() => setDeleteTarget(p)}
                        >
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                ))}
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

      {/* Edit dialog */}
      {editTarget && (
        <Dialog open onOpenChange={(o) => !o && setEditTarget(null)}>
          <DialogContent className="sm:max-w-lg">
            <DialogHeader><DialogTitle>Editar produto</DialogTitle></DialogHeader>
            <ProductForm
              initial={productToForm(editTarget)}
              onSave={(f) => updateMut.mutate({ id: editTarget.id, form: f })}
              saving={updateMut.isPending}
              onCancel={() => setEditTarget(null)}
            />
          </DialogContent>
        </Dialog>
      )}

      {/* Delete confirm */}
      {deleteTarget && (
        <Dialog open onOpenChange={(o) => !o && setDeleteTarget(null)}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Deletar "{deleteTarget.name}"?</DialogTitle>
            </DialogHeader>
            <p className="text-sm text-muted-foreground">Esta ação não pode ser desfeita.</p>
            <DialogFooter>
              <Button variant="outline" onClick={() => setDeleteTarget(null)}>Cancelar</Button>
              <Button
                variant="destructive"
                onClick={() => deleteMut.mutate(deleteTarget.id)}
                disabled={deleteMut.isPending}
              >
                {deleteMut.isPending ? "Deletando…" : "Deletar"}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      )}
    </AdminLayout>
  );
};

export default AdminProducts;
