# Architecture

Project9 uses a DDD-inspired Laravel architecture with four main layers: Domain, Application, Infrastructure, and Interfaces.

## Domain Layer

Path: `app/Domain`

The Domain layer contains core business concepts and rules:

- Entities: `User`, `Product`, `Order`, `OrderItem`, `Payment`.
- Value Objects: `Email`, `UserRole`, `ProductStatus`, `OrderStatus`, `Quantity`, `PaymentStatus`, `PaymentProvider`.
- Repository interfaces.
- Domain exceptions.
- Domain events.

Domain classes do not depend on HTTP controllers or Eloquent models.

## Application Layer

Path: `app/Application`

The Application layer contains use cases and DTOs. Use cases coordinate business operations through repository interfaces.

Examples:

- `RegisterUserAction`
- `CreateProductAction`
- `CreateOrderAction`
- `CancelOrderAction`
- `PayOrderAction`
- `GetUserPaymentsAction`

DTOs normalize request data before it reaches a use case.

## Infrastructure Layer

Path: `app/Infrastructure`

The Infrastructure layer contains technical details:

- Eloquent models.
- Eloquent repository implementations.
- Fake payment provider.
- Queue jobs.
- Event listeners.

Eloquent access is isolated here. Controllers and use cases depend on repository interfaces, not on Eloquent directly.

## Interfaces Layer

Path: `app/Interfaces`

The Interfaces layer contains HTTP integration:

- Controllers.
- Form Requests.
- API Resources.
- HTTP middleware.

Controllers are intentionally thin. They validate input through Form Requests, build DTOs, call use cases, and return API Resources.

## Repository Pattern

Repository interfaces live in the Domain layer:

- `UserRepositoryInterface`
- `ProductRepositoryInterface`
- `OrderRepositoryInterface`
- `PaymentRepositoryInterface`

Implementations live in Infrastructure:

- `UserEloquentRepository`
- `ProductEloquentRepository`
- `OrderEloquentRepository`
- `PaymentEloquentRepository`

Bindings are registered in `AppServiceProvider`. This keeps application code independent from persistence details.

## DTOs

DTOs live in `app/Application/*/DTO`.

They provide typed input for use cases:

- `RegisterUserDTO`
- `LoginUserDTO`
- `CreateProductDTO`
- `UpdateProductDTO`
- `CreateOrderDTO`
- `CreateOrderItemDTO`
- `PayOrderDTO`

Form Requests validate HTTP input, then DTOs carry clean data into the Application layer.

## Value Objects

Value Objects protect important invariants:

- `Email` lowercases and validates email addresses.
- `UserRole` accepts only supported user roles.
- `ProductStatus` accepts only `active` or `inactive`.
- `Quantity` rejects zero and negative values.
- `OrderStatus` accepts only `pending`, `paid`, `cancelled`, `completed`.
- `PaymentStatus` accepts only `pending`, `success`, `failed`.
- `PaymentProvider` currently accepts only `fake`.

## Events, Listeners, And Queue Jobs

Domain events:

- `OrderCreated`
- `OrderCancelled`
- `OrderPaid`
- `PaymentFailed`
- `ProductStockReduced`
- `ProductStockRestored`

Listeners:

- `ReduceProductStock`
- `RestoreProductStock`
- `SendOrderPaidNotification`
- `WriteOrderLog`
- `WritePaymentLog`

Queue jobs:

- `SendOrderPaidEmailJob`
- `WriteOrderLogJob`
- `ProcessPaymentWebhookJob`

Order and payment repositories dispatch domain events after important state changes. Listeners push queued jobs for logs and notifications.

## Business Flow

1. User registers through `POST /api/register`.
2. User logs in through `POST /api/login` and receives a Sanctum bearer token.
3. User lists active products through `GET /api/products`.
4. User creates an order through `POST /api/orders`.
5. `CreateOrderAction` delegates to `OrderRepositoryInterface`.
6. `OrderEloquentRepository` validates product availability, calculates totals, decreases stock in a database transaction, and dispatches `OrderCreated` and `ProductStockReduced`.
7. `WriteOrderLog` receives `OrderCreated` and dispatches `WriteOrderLogJob`.
8. User pays the pending order through `POST /api/orders/{id}/pay`.
9. `PayOrderAction` delegates to `PaymentRepositoryInterface`.
10. `PaymentEloquentRepository` validates order ownership/status/amount, calls `FakePaymentProvider`, creates a payment, marks the order as `paid` on success, and dispatches `OrderPaid`.
11. `SendOrderPaidNotification` receives `OrderPaid` and dispatches `SendOrderPaidEmailJob`.
12. `WriteOrderLog` receives `OrderPaid` and dispatches `WriteOrderLogJob`.

If fake payment fails, `PaymentFailed` is dispatched and `WritePaymentLog` queues a log job.
