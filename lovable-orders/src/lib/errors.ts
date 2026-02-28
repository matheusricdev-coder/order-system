import { ApiError } from "@/types/api";

export interface CheckoutErrorInfo {
    title: string;
    description: string;
    suggestion: string;
    canRetry: boolean;
}

const ERROR_MAP: Record<string, CheckoutErrorInfo> = {
    INSUFFICIENT_STOCK: {
        title: "Produto sem estoque suficiente",
        description:
            "Um ou mais itens do seu carrinho não possuem estoque disponível no momento.",
        suggestion:
            "Remova ou reduza a quantidade dos itens indisponíveis e tente novamente.",
        canRetry: false,
    },
    PRODUCT_NOT_FOUND: {
        title: "Produto não encontrado",
        description:
            "Um ou mais produtos do seu carrinho não estão mais disponíveis.",
        suggestion: "Atualize seu carrinho e continue comprando.",
        canRetry: false,
    },
    STOCK_NOT_FOUND: {
        title: "Estoque não encontrado",
        description:
            "Não foi possível verificar a disponibilidade de um produto.",
        suggestion: "Tente novamente em alguns instantes.",
        canRetry: true,
    },
    UNAUTHENTICATED: {
        title: "Sessão expirada",
        description: "Você precisa estar autenticado para finalizar a compra.",
        suggestion: "Faça login novamente para continuar.",
        canRetry: false,
    },
    INVALID_ORDER_TRANSITION: {
        title: "Pedido em estado inválido",
        description: "Este pedido não pode ser processado no estado atual.",
        suggestion: "Verifique seus pedidos ou inicie uma nova compra.",
        canRetry: false,
    },
    INTERNAL_ERROR: {
        title: "Erro interno do servidor",
        description: "Nosso sistema encontrou um problema inesperado.",
        suggestion: "Por favor, tente novamente em alguns instantes.",
        canRetry: true,
    },
};

const DEFAULT_ERROR: CheckoutErrorInfo = {
    title: "Não foi possível processar seu pedido",
    description: "Ocorreu um problema ao iniciar o checkout.",
    suggestion:
        "Tente novamente ou volte ao catálogo para continuar comprando.",
    canRetry: true,
};

export function getCheckoutErrorInfo(err: unknown): CheckoutErrorInfo {
    if (err instanceof ApiError) {
        return ERROR_MAP[err.body.code] ?? DEFAULT_ERROR;
    }
    return DEFAULT_ERROR;
}
