# Order System API

API de marketplace construída em **Laravel 12 + PHP 8.3** com foco em **qualidade arquitetural**, **consistência transacional** e boas práticas de **DDD (Domain-Driven Design)** para cenários reais de pedidos, estoque e autenticação.

> Projeto de portfólio com abordagem profissional para demonstrar domínio de arquitetura, design de software e testes automatizados.

> **Demo ao vivo:** [ordexa-sys.vercel.app](https://ordexa-sys.vercel.app) · Frontend: [lovable-orders](https://github.com/seu-usuario/lovable-orders)

---

## 📌 Visão Geral

O projeto implementa um núcleo de e-commerce com:

- Catálogo público de produtos, categorias e empresas.
- Gestão de pedidos com ciclo de vida explícito (`created -> paid/cancelled`).
- Controle de estoque com **reserva**, **consumo** e **liberação**.
- API autenticada com **Laravel Sanctum**.
- Observabilidade básica por **Correlation ID**.
- Especificação OpenAPI disponível em `docs/openapi.yaml`.

### Principais objetivos técnicos

- Evitar inconsistências de estado com uso de transações e lock pessimista.
- Separar regras de negócio do framework por meio de camadas e contratos.
- Expor um código legível e sustentável para evolução do produto.

---

## 🧱 Arquitetura

O código segue uma combinação de:

- **Clean Architecture**
- **Hexagonal (Ports & Adapters)**
- **DDD tático**

### Camadas

- **Domain (`app/Domain`)**: entidades, value objects, enums, eventos e invariantes de negócio.
- **Application (`app/Application`)**: casos de uso (handlers), comandos/queries, portas (interfaces) e orquestração transacional.
- **Infrastructure (`app/Infrastructure`)**: repositórios Eloquent, barramento de eventos e transações Laravel.
- **Interface (`app/Http`, `routes`)**: controllers, requests, middleware e endpoints REST.

### Direção de dependências

```text
Interface -> Application -> Domain
Infrastructure -> Application -> Domain
```

A camada de domínio não depende de Laravel/Eloquent.

---

## ✅ Regras de Negócio Implementadas

### Pedido (Order)

- Um pedido inicia em `created`.
- Só pode transitar para `paid` ou `cancelled`.
- Transições inválidas geram exceção de domínio.
- Total é calculado com `Money` (valor em centavos + moeda).

### Estoque (Stock)

- Nunca pode ficar negativo.
- Reserva exige disponibilidade.
- Consumo só ocorre sobre quantidade previamente reservada.
- Cancelamento de pedido libera reserva.

### Usuário

- Usuário inativo não pode criar pedidos.
- Endpoints de pedidos exigem autenticação.

---

## 🗂️ Estrutura de Pastas (resumo)

```text
app/
├── Domain/                # Entidades, VO, eventos, regras
├── Application/           # Casos de uso, DTOs, portas
├── Infrastructure/        # Adapters concretos (Eloquent, transações, event bus)
└── Http/                  # API controllers, requests e middleware

database/
├── migrations/            # Schema, constraints e relacionamentos
└── seeders/               # Dados iniciais de catálogo/admin

tests/
├── Unit/                  # Testes de domínio e aplicação
└── Feature/               # Testes HTTP end-to-end

docs/
└── openapi.yaml           # Contrato da API
```

---

## 🚀 Como Executar Localmente

### Pré-requisitos

- PHP 8.3+
- Composer
- Banco de dados (SQLite, MySQL ou PostgreSQL)
- Node.js 18+ (apenas para build de assets)

### Passo a passo

1. Clone o repositório:

```bash
git clone https://github.com/seu-usuario/ordem-system.git
cd ordem-system
```

2. Instale dependências PHP:

```bash
composer install
```

3. Configure ambiente:

```bash
cp .env.example .env
php artisan key:generate
```

4. Configure o banco de dados no `.env` (SQLite já vem pré-configurado) e rode migrations:

```bash
php artisan migrate --seed
```

5. Suba o servidor:

```bash
php artisan serve
```

API disponível em: `http://127.0.0.1:8000/api/v1`

---

## 🔐 Autenticação

Fluxo padrão:

1. `POST /api/v1/auth/register`
2. `POST /api/v1/auth/login`
3. Usar token Bearer retornado no header:

```http
Authorization: Bearer <token>
```

Endpoints protegidos:

- `/api/v1/orders/*`
- `/api/v1/me`
- `/api/v1/me/login-streak`
- `/api/v1/auth/logout`

---

## 📚 Endpoints Principais

### Catálogo (público)

- `GET /api/v1/products`
- `GET /api/v1/products/{id}`
- `GET /api/v1/categories`
- `GET /api/v1/companies/{id}`
- `GET /api/v1/companies/{id}/products`
- `GET /api/v1/stocks/{productId}`
- `GET /api/v1/products/{id}/stock`

### Pedidos (autenticado)

- `POST /api/v1/orders`
- `POST /api/v1/orders/{id}/pay`
- `POST /api/v1/orders/{id}/cancel`
- `GET /api/v1/orders/{id}`
- `GET /api/v1/orders`

### Infra

- `GET /api/health`

---

## 🧪 Qualidade e Testes

A suíte cobre:

- Regras de domínio (`Order`, `OrderItem`, `Stock`, `Money`, `User`).
- Casos de uso da aplicação (create/pay/cancel/list/get).
- Fluxos HTTP críticos (auth, catálogo e pedidos).

Comandos úteis:

```bash
php artisan test
./vendor/bin/phpunit
```

---

## 🧭 Observabilidade e Resiliência

- Middleware de **Correlation ID** (`X-Correlation-Id`) para rastreabilidade.
- Mapeamento consistente de exceções para respostas JSON.
- Códigos HTTP alinhados ao tipo de erro (validação, autorização, domínio etc.).

---

## 🛠️ Deploy

O repositório possui configurações para deploy no **Fly.io** (`fly.toml`, `Dockerfile`).

URL de produção: `https://ordem-system-api.fly.dev`

Outros arquivos de configuração presentes:
- `nixpacks.toml`

## 💻 Frontend

Este projeto possui uma interface web completa integrada a esta API.

- **Demo:** [ordexa-sys.vercel.app](https://ordexa-sys.vercel.app)
