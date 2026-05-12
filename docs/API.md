# API Documentation

Base URL examples use:

```text
http://localhost:8000
```

Authenticated endpoints require a Sanctum bearer token:

```http
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

## Public Routes

| Method | Endpoint | Description |
| --- | --- | --- |
| `GET` | `/api/products` | List active products |
| `GET` | `/api/products/{id}` | Show active product |

```bash
curl -X GET http://localhost:8000/api/products \
  -H "Accept: application/json"
```

## Auth Routes

| Method | Endpoint | Auth | Description |
| --- | --- | --- | --- |
| `POST` | `/api/register` | No | Register user and return token |
| `POST` | `/api/login` | No | Login and return token |
| `POST` | `/api/logout` | Bearer | Delete current token |
| `GET` | `/api/me` | Bearer | Current user |

Register:

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
  }'
```

Login:

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }'
```

Logout:

```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

## Product Routes

| Method | Endpoint | Auth | Description |
| --- | --- | --- | --- |
| `GET` | `/api/products` | No | List active products |
| `GET` | `/api/products/{id}` | No | Show active product |

## Admin Product Routes

Admin routes require `auth:sanctum` and `role=admin`.

| Method | Endpoint | Description |
| --- | --- | --- |
| `GET` | `/api/admin/products` | List all products |
| `POST` | `/api/admin/products` | Create product |
| `GET` | `/api/admin/products/{id}` | Show any product |
| `PUT` | `/api/admin/products/{id}` | Update product |
| `DELETE` | `/api/admin/products/{id}` | Delete product |

Create product:

```bash
curl -X POST http://localhost:8000/api/admin/products \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -d '{
    "name": "New Product",
    "description": "Product description",
    "price": 2000,
    "stock": 10,
    "status": "active"
  }'
```

Update product:

```bash
curl -X PUT http://localhost:8000/api/admin/products/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <admin-token>" \
  -d '{
    "name": "Updated Product",
    "description": null,
    "price": 2500,
    "stock": 5,
    "status": "inactive"
  }'
```

## Order Routes

Order routes require bearer auth.

| Method | Endpoint | Description |
| --- | --- | --- |
| `POST` | `/api/orders` | Create order |
| `GET` | `/api/orders` | List current user's orders |
| `GET` | `/api/orders/{id}` | Show current user's order |
| `POST` | `/api/orders/{id}/cancel` | Cancel pending order |
| `POST` | `/api/orders/{id}/pay` | Pay pending order |

Create order:

```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{
    "items": [
      {
        "product_id": 1,
        "quantity": 2
      }
    ]
  }'
```

Cancel order:

```bash
curl -X POST http://localhost:8000/api/orders/1/cancel \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

## Admin Order Routes

Admin routes require `auth:sanctum` and `role=admin`.

| Method | Endpoint | Description |
| --- | --- | --- |
| `GET` | `/api/admin/orders` | List all orders |
| `GET` | `/api/admin/orders/{id}` | Show any order |
| `POST` | `/api/admin/orders/{id}/complete` | Complete paid order |

Complete paid order:

```bash
curl -X POST http://localhost:8000/api/admin/orders/1/complete \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <admin-token>"
```

## Payment Routes

Payment routes require bearer auth. Users can only see their own payments.

| Method | Endpoint | Description |
| --- | --- | --- |
| `POST` | `/api/orders/{id}/pay` | Pay current user's pending order |
| `GET` | `/api/payments` | List current user's payments |
| `GET` | `/api/payments/{id}` | Show current user's payment |

Pay order:

```bash
curl -X POST http://localhost:8000/api/orders/1/pay \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{
    "amount": 1000,
    "provider": "fake"
  }'
```

List payments:

```bash
curl -X GET http://localhost:8000/api/payments \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

## Status Values

Product status:

```json
["active", "inactive"]
```

Order status:

```json
["pending", "paid", "cancelled", "completed"]
```

Payment status:

```json
["pending", "success", "failed"]
```

Payment provider:

```json
["fake"]
```
