# Instructions for GitHub Copilot

## Project Context
- This project is a full-stack order and payment system (mini marketplace like iFood or Mercado Livre).
- The goal is to create maintainable, testable, and professional-quality code that demonstrates especialist level skills.

## Stack
- Backend: PHP 8+ / Laravel 10
- Frontend: JavaScript (Blade + JS, Vue 3, or React)
- Database: MySQL or Postgres (utf8mb4)
- Auth: Laravel Sanctum (API tokens)
- Dev Tools: Composer, NPM, Laravel Breeze
- Observability: Logs, error handling, and consistent messaging

## Code Objectives
- Controllers orchestrate requests and responses
- Services/UseCases handle business logic
- Repositories handle database access
- Models represent entities
- All endpoints should be RESTful

## Principles / Best Practices
- Use PSR-12 coding standard
- Clear naming conventions for classes, methods, variables
- Avoid magic numbers and hardcoded strings
- Separate business logic from controllers
- Validate all input using Laravel Form Requests
- Transactions for checkout and stock update
- Concurrency control when updating stock
- Log critical events (order created, payment succeeded/failed)
- Provide HTTP status codes and error messages consistently
- Include migrations, seeders, and factories for testing
- Write unit and integration tests

## Structure Examples
- Models: User, Product, Cart, CartItem, Order, OrderItem
- Controllers: ProductController, CartController, OrderController, AuthController
- Services: OrderService
- Migrations: well-named and versioned
- Routes: `/api/products`, `/api/cart`, `/api/orders`
- Frontend: consume API endpoints; Blade or SPA structure

## Notes / Constraints
- Treat the project as a **pleno-level showcase**
- Modular, testable, and maintainable code only
- Do not write code that only works locally without proper structure
- Include documentation in README: problem, decisions, endpoints, payloads, examples
