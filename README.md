# Project9

Project9 is a Laravel REST API project for products, orders, and payments.

The codebase is being organized around a DDD-inspired structure:

- `app/Domain` for domain modules and business concepts.
- `app/Application` for application use cases and DTO orchestration.
- `app/Infrastructure` for persistence, services, and queue integration.
- `app/Interfaces` for HTTP controllers, requests, and API resources.

Stage 1 contains only the base structure and Laravel Sanctum installation.
Business logic is intentionally not implemented yet.
