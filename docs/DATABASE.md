# Tubes-PEC — Database Design v1.0

**Status:** Approved conceptual design; not yet implemented in Laravel migrations.  
**Application:** Tubes-PEC — Hybrid Technology E-Commerce  
**Framework:** Laravel 13  
**Primary database:** MySQL / InnoDB  
**ORM:** Eloquent

## 1. Design goals

This schema supports the required academic e-commerce workflow: Admin and Customer roles, category and product management, catalog browsing, shopping cart, checkout, order history, Midtrans payment verification, and order fulfillment management.

Design priorities:

- Preserve order and payment history even when a catalog product changes or is removed.
- Calculate prices and totals on the server, never from browser-submitted totals.
- Prevent overselling through transactional stock reservation.
- Separate payment state from fulfillment state.
- Keep payment attempts and payment notifications auditable and idempotent.
- Keep the MVP understandable and avoid tables for features that are not currently required.

## 2. Entity Relationship Diagram

```mermaid
erDiagram
    USERS ||--o| CARTS : owns
    USERS ||--o{ ORDERS : places
    CATEGORIES ||--o{ PRODUCTS : contains
    CARTS ||--o{ CART_ITEMS : contains
    PRODUCTS ||--o{ CART_ITEMS : selected_in
    ORDERS ||--|{ ORDER_ITEMS : contains
    PRODUCTS o|--o{ ORDER_ITEMS : snapshot_source
    ORDERS ||--o{ PAYMENTS : has_attempts
    PAYMENTS ||--o{ PAYMENT_NOTIFICATIONS : receives

    USERS {
        bigint id PK
        varchar name
        varchar email UK
        varchar password
        varchar role
        varchar phone
        timestamp email_verified_at
        timestamps timestamps
    }
    CATEGORIES {
        bigint id PK
        varchar name
        varchar slug UK
        text description
        boolean is_active
        timestamps timestamps
    }
    PRODUCTS {
        bigint id PK
        bigint category_id FK
        varchar sku UK
        varchar name
        varchar slug UK
        text description
        json specifications
        decimal price
        unsigned_int stock
        varchar image_path
        boolean is_active
        soft_deletes deleted_at
        timestamps timestamps
    }
    CARTS {
        bigint id PK
        bigint user_id FK_UK
        timestamps timestamps
    }
    CART_ITEMS {
        bigint id PK
        bigint cart_id FK
        bigint product_id FK
        unsigned_int quantity
        timestamps timestamps
    }
    ORDERS {
        bigint id PK
        bigint user_id FK
        varchar order_number UK
        varchar recipient_name
        varchar recipient_phone
        text shipping_address
        varchar shipping_city
        varchar shipping_province
        varchar shipping_postal_code
        text notes
        decimal subtotal
        decimal shipping_cost
        decimal total_amount
        char currency
        varchar payment_status
        varchar fulfillment_status
        varchar inventory_status
        timestamp inventory_reserved_until
        timestamp cancelled_at
        timestamps timestamps
    }
    ORDER_ITEMS {
        bigint id PK
        bigint order_id FK
        bigint product_id FK_NULL
        varchar product_name
        varchar product_sku
        varchar product_image_path
        decimal unit_price
        unsigned_int quantity
        decimal line_total
        timestamps timestamps
    }
    PAYMENTS {
        bigint id PK
        bigint order_id FK
        varchar provider
        varchar provider_order_id UK
        varchar provider_transaction_id UK_NULL
        varchar payment_method
        decimal amount
        char currency
        varchar status
        varchar provider_status
        varchar fraud_status
        text snap_token
        text redirect_url
        text qr_code_url
        timestamp paid_at
        timestamp expired_at
        json metadata
        timestamps timestamps
    }
    PAYMENT_NOTIFICATIONS {
        bigint id PK
        bigint payment_id FK
        char notification_key UK
        varchar provider_order_id
        varchar provider_transaction_id
        varchar transaction_status
        varchar fraud_status
        decimal gross_amount
        varchar processing_status
        timestamp received_at
        timestamp processed_at
        timestamps timestamps
    }
```

**Notation:** `FK_NULL` means a nullable foreign key. `FK_UK` means a foreign key with a unique constraint. `timestamps` represents Laravel's `created_at` and `updated_at` columns.

## 3. Tables and responsibilities

### 3.1 `users`

Extends Laravel's existing users table. One table is used for both roles.

| Column | Suggested definition | Rules / purpose |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK | Existing Laravel primary key |
| `name` | VARCHAR(255) | Required |
| `email` | VARCHAR(255), UNIQUE | Required; existing unique constraint |
| `password` | VARCHAR(255) | Store only a password hash |
| `role` | VARCHAR(20), default `customer`, indexed | Allowed application values: `admin`, `customer` |
| `phone` | VARCHAR(30), nullable | Basic contact number |
| `email_verified_at` | TIMESTAMP, nullable | Existing Laravel field |
| `remember_token` | VARCHAR(100), nullable | Existing Laravel field |
| `created_at`, `updated_at` | TIMESTAMP | Existing Laravel timestamps |

**Security rule:** public registration must assign `customer` on the server. Never accept `role=admin` from a public registration request. Admin accounts must be provisioned through a trusted seeder or controlled administrative process.

### 3.2 `categories`

| Column | Suggested definition | Rules / purpose |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK | Primary key |
| `name` | VARCHAR(120) | Required |
| `slug` | VARCHAR(150), UNIQUE | URL-friendly identifier |
| `description` | TEXT, nullable | Category description |
| `is_active` | BOOLEAN, default true | Controls catalog visibility |
| timestamps | TIMESTAMP pair | Laravel timestamps |

Initial categories: Laptops & Computers, Peripherals, Audio & Display, Storage & Accessories. These are seed data, not fixed database enums.

### 3.3 `products`

| Column | Suggested definition | Rules / purpose |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK | Primary key |
| `category_id` | BIGINT UNSIGNED, FK | References `categories.id`; restrict category deletion while products exist |
| `sku` | VARCHAR(64), UNIQUE | Unique stock-keeping code; decide whether required in admin validation |
| `name` | VARCHAR(180) | Required |
| `slug` | VARCHAR(200), UNIQUE | Product URL identifier |
| `description` | TEXT | Product description |
| `specifications` | JSON, nullable | Category-specific specifications, cast to array in Eloquent |
| `price` | DECIMAL(15,2) | Non-negative price; never FLOAT/DOUBLE for money |
| `stock` | INT UNSIGNED | Available quantity; updates must be transactional |
| `image_path` | VARCHAR(255) | Storage path, not a raw public filesystem path |
| `is_active` | BOOLEAN, default true | Controls customer visibility |
| `deleted_at` | TIMESTAMP, nullable | Laravel SoftDeletes |
| timestamps | TIMESTAMP pair | Laravel timestamps |

Normal product removal should use soft deletes. The order item keeps a product snapshot, so past invoices remain readable. If a product is ever hard-deleted, `order_items.product_id` becomes NULL; the snapshot fields remain.

### 3.4 `carts`

| Column | Suggested definition | Rules / purpose |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK | Primary key |
| `user_id` | BIGINT UNSIGNED, UNIQUE, FK | One active cart per authenticated customer; cascade when a user is permanently deleted |
| timestamps | TIMESTAMP pair | Laravel timestamps |

For the MVP, customers must sign in to use a persistent cart. Public catalog browsing does not require authentication.

### 3.5 `cart_items`

| Column | Suggested definition | Rules / purpose |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK | Primary key |
| `cart_id` | BIGINT UNSIGNED, FK | References `carts.id`, cascade on cart deletion |
| `product_id` | BIGINT UNSIGNED, FK | References `products.id`, cascade on hard product deletion |
| `quantity` | INT UNSIGNED | Must be greater than zero |
| timestamps | TIMESTAMP pair | Laravel timestamps |

Add a unique composite constraint on (`cart_id`, `product_id`) so the same product has only one row in a cart. Cart prices are not authoritative; checkout recalculates prices from the database.

### 3.6 `orders`

| Column | Suggested definition | Rules / purpose |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK | Primary key |
| `user_id` | BIGINT UNSIGNED, FK | Customer who placed the order; restrict permanent user deletion |
| `order_number` | VARCHAR(40), UNIQUE | Public, non-sequential order reference |
| `recipient_name` | VARCHAR(150) | Address/contact snapshot |
| `recipient_phone` | VARCHAR(30) | Recipient contact snapshot |
| `shipping_address` | TEXT | Full address snapshot |
| `shipping_city` | VARCHAR(100) | Destination city |
| `shipping_province` | VARCHAR(100) | Destination province |
| `shipping_postal_code` | VARCHAR(15) | Postal code |
| `notes` | TEXT, nullable | Customer delivery note |
| `subtotal` | DECIMAL(15,2) | Sum of order item line totals |
| `shipping_cost` | DECIMAL(15,2), default 0 | MVP may use zero/manual shipping cost |
| `total_amount` | DECIMAL(15,2) | Server-calculated payable total |
| `currency` | CHAR(3), default `IDR` | Currency code |
| `payment_status` | VARCHAR(24), indexed | Order-level summary of authoritative payment state |
| `fulfillment_status` | VARCHAR(24), indexed | Operational processing/shipping state |
| `inventory_status` | VARCHAR(16) | `reserved`, `committed`, or `released` |
| `inventory_reserved_until` | TIMESTAMP, nullable | Deadline for holding stock |
| `cancelled_at` | TIMESTAMP, nullable | Cancellation timestamp |
| timestamps | TIMESTAMP pair | Laravel timestamps |

Recipient and shipping fields are snapshots. Changing the customer's profile must not modify a previous order.

### 3.7 `order_items`

| Column | Suggested definition | Rules / purpose |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK | Primary key |
| `order_id` | BIGINT UNSIGNED, FK | References `orders.id`, cascade only if order deletion is explicitly allowed by retention policy |
| `product_id` | BIGINT UNSIGNED, nullable FK | References `products.id`, set NULL on hard product deletion |
| `product_name` | VARCHAR(180) | Name snapshot |
| `product_sku` | VARCHAR(64), nullable | SKU snapshot |
| `product_image_path` | VARCHAR(255), nullable | Image path snapshot |
| `unit_price` | DECIMAL(15,2) | Price at purchase time |
| `quantity` | INT UNSIGNED | Quantity purchased; greater than zero |
| `line_total` | DECIMAL(15,2) | Snapshot of unit price multiplied by quantity |
| timestamps | TIMESTAMP pair | Laravel timestamps |

The snapshot columns preserve historical order details when product name, price, SKU, or image changes. `line_total` is intentionally stored as a transaction snapshot; validate that it equals `unit_price * quantity` when creating the order.

### 3.8 `payments`

One order can have multiple payment attempts. Each attempt receives a unique `provider_order_id` sent to Midtrans.

| Column | Suggested definition | Rules / purpose |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK | Primary key |
| `order_id` | BIGINT UNSIGNED, FK | References `orders.id`; retain transaction history |
| `provider` | VARCHAR(30), default `midtrans` | Payment provider |
| `provider_order_id` | VARCHAR(64), UNIQUE | Unique merchant transaction reference for each attempt |
| `provider_transaction_id` | VARCHAR(100), nullable, UNIQUE | Midtrans transaction identifier; nullable until returned |
| `payment_method` | VARCHAR(30), nullable | For example QRIS |
| `amount` | DECIMAL(15,2) | Must match the order total for the attempt |
| `currency` | CHAR(3), default `IDR` | Currency code |
| `status` | VARCHAR(24), indexed | Normalized internal status |
| `provider_status` | VARCHAR(40), nullable | Provider's original transaction status |
| `fraud_status` | VARCHAR(30), nullable | Provider fraud status when applicable |
| `snap_token` | TEXT, nullable | Store only if the selected Midtrans flow requires it |
| `redirect_url` | TEXT, nullable | Checkout redirect URL when applicable |
| `qr_code_url` | TEXT, nullable | QR display resource when applicable |
| `paid_at` | TIMESTAMP, nullable | Verified payment time |
| `expired_at` | TIMESTAMP, nullable | Payment attempt expiry |
| `metadata` | JSON, nullable | Minimal, sanitized provider metadata; never store secrets |
| timestamps | TIMESTAMP pair | Laravel timestamps |

Do not assume the final Midtrans integration flow yet. Select Snap or Core API after checking the exact QRIS requirements. Keep provider-specific details behind a payment service so the order logic does not depend on one response format.

### 3.9 `payment_notifications`

Store normalized, verified notification events for traceability and idempotency. Do not store unrestricted raw request bodies or credentials.

| Column | Suggested definition | Rules / purpose |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK | Primary key |
| `payment_id` | BIGINT UNSIGNED, FK | References `payments.id` |
| `notification_key` | CHAR(64), UNIQUE | SHA-256 key derived from stable, normalized event fields |
| `provider_order_id` | VARCHAR(64) | Provider transaction reference |
| `provider_transaction_id` | VARCHAR(100), nullable | Provider transaction identifier |
| `transaction_status` | VARCHAR(40) | Provider status received |
| `fraud_status` | VARCHAR(30), nullable | Provider fraud status |
| `gross_amount` | DECIMAL(15,2) | Amount received in the notification |
| `processing_status` | VARCHAR(20) | `received`, `processed`, `ignored`, or `failed` |
| `received_at` | TIMESTAMP | Receipt time |
| `processed_at` | TIMESTAMP, nullable | Processing completion time |
| timestamps | TIMESTAMP pair | Laravel timestamps |

Invalid signatures should be rejected and logged through application security logging. They must never update a payment or order. Only signature-verified notifications should be inserted into this audit table, so a `signature_valid` column is intentionally omitted as redundant.

## 4. Relationship and deletion policy

| Parent | Child | Cardinality | Deletion policy |
|---|---|---|---|
| users | carts | 1 : 0..1 | Cascade only on permanent user deletion |
| users | orders | 1 : N | Restrict permanent user deletion |
| categories | products | 1 : N | Restrict category deletion while products exist |
| carts | cart_items | 1 : N | Cascade |
| products | cart_items | 1 : N | Cascade on permanent product deletion; soft delete is preferred |
| orders | order_items | 1 : N | Restrict hard deletion; preserve transaction history |
| products | order_items | 1 : N | Set product reference NULL on permanent deletion; preserve snapshots |
| orders | payments | 1 : N | Preserve payment history; avoid routine order deletion |
| payments | payment_notifications | 1 : N | Restrict routine payment deletion; retain notification audit history |

A category cannot be deleted while products still reference it. The admin must reassign or remove those products first. Prefer deactivation over deletion where historical or operational data exists.

## 5. Status lifecycle

### Payment status

Suggested normalized values: `pending`, `paid`, `failed`, `expired`, `cancelled`, `refunded`, `partially_refunded`.

The individual `payments.status` records each attempt. `orders.payment_status` is a denormalized order-level summary and must only be updated by trusted server-side payment processing rules, not by a customer or arbitrary admin form.

Map Midtrans provider statuses to internal states in one service. Do not assume every provider status maps directly to a terminal state. Handle capture fraud status and settlement rules according to current Midtrans documentation.

### Fulfillment status

Suggested values: `unprocessed`, `processing`, `shipped`, `completed`, `cancelled`.

Payment success does not mean the order has shipped. Keep these states independent.

### Inventory status

Suggested values: `reserved`, `committed`, `released`.

Recommended MVP stock policy:

1. At checkout, run a database transaction and lock relevant product rows.
2. Recalculate quantities, prices, and totals from the database.
3. Confirm sufficient stock.
4. Reduce available stock and mark the order inventory as `reserved`; set `inventory_reserved_until`.
5. On verified payment success, change the reservation to `committed` without reducing stock again.
6. On final expiry/cancellation, restore stock only once and mark the reservation `released`.
7. If a payment attempt expires but another valid attempt remains active, do not release the order's reservation prematurely.

A scheduled expiration/reconciliation process will be needed later to release reservations whose deadline has passed and which have no active payment attempt. This mechanism is not implemented in this design document.

## 6. Indexes and constraints

At minimum, implement:

- Unique `users.email` (already present in the Laravel starter migration).
- Index `users.role`.
- Unique `categories.slug`.
- Unique `products.slug` and `products.sku`.
- Index `products.category_id`, `products.is_active`, and useful catalog search columns after evaluating query patterns.
- Unique `carts.user_id`.
- Unique composite `cart_items(cart_id, product_id)`.
- Index `cart_items.product_id`.
- Unique `orders.order_number`.
- Index `orders(user_id, created_at)`.
- Index `orders.payment_status` and `orders.fulfillment_status`.
- Index `orders.inventory_status` and `inventory_reserved_until` for expiration processing.
- Index `order_items.order_id` and `order_items.product_id`.
- Unique `payments.provider_order_id`.
- Unique nullable `payments.provider_transaction_id` (confirm MySQL multiple-NULL behavior in migration tests).
- Index `payments(order_id, status)`.
- Unique `payment_notifications.notification_key`.
- Index `payment_notifications.payment_id` and `received_at`.

Use InnoDB and `utf8mb4`. Use foreign keys with explicit deletion behavior. Do not rely on application validation alone for uniqueness or referential integrity.

## 7. Eloquent relationship map

Suggested relationships to implement after migrations:

- `User::cart()` — `hasOne(Cart::class)`
- `User::orders()` — `hasMany(Order::class)`
- `Category::products()` — `hasMany(Product::class)`
- `Product::category()` — `belongsTo(Category::class)`
- `Cart::user()` — `belongsTo(User::class)`
- `Cart::items()` — `hasMany(CartItem::class)`
- `CartItem::cart()` — `belongsTo(Cart::class)`
- `CartItem::product()` — `belongsTo(Product::class)`
- `Order::user()` — `belongsTo(User::class)`
- `Order::items()` — `hasMany(OrderItem::class)`
- `Order::payments()` — `hasMany(Payment::class)`
- `OrderItem::order()` — `belongsTo(Order::class)`
- `OrderItem::product()` — `belongsTo(Product::class)`
- `Payment::order()` — `belongsTo(Order::class)`
- `Payment::notifications()` — `hasMany(PaymentNotification::class)`
- `PaymentNotification::payment()` — `belongsTo(Payment::class)`

Add explicit casts for decimal, JSON, boolean, and datetime attributes as appropriate. Do not expose password hashes, tokens, or payment secrets in serialized models.

## 8. Implementation sequence

Implement this design in small, reviewable steps:

1. Verify local MySQL server and PHP `pdo_mysql` extension.
2. Create an empty local database named `tubes_pec` using `utf8mb4_unicode_ci`.
3. Configure local `.env` for MySQL; never commit `.env`.
4. Change `.env.example` to document safe MySQL defaults (no passwords or secrets).
5. Verify the connection without running migrations.
6. Add a migration extending `users` with `role` and `phone`.
7. Add catalog migrations (`categories`, `products`).
8. Add cart migrations (`carts`, `cart_items`).
9. Add order migrations (`orders`, `order_items`).
10. Add payment migrations (`payments`, `payment_notifications`).
11. Add Eloquent relationships and casts.
12. Add factories, seeders, and database tests.

Do not run destructive commands such as `migrate:fresh` or `db:wipe` without explicit approval. Keep the existing Laravel starter migrations and add new migrations rather than rewriting applied migration history.

## 9. Open implementation decisions

Before coding migrations, confirm these details:

- The local MySQL server version and active port.
- Whether PHP CLI has `pdo_mysql` enabled.
- Whether the product SKU will be required in the admin form (recommended: required and unique).
- Whether the MVP shipping cost is always zero/manual (recommended initially: store a value on each order, default zero).
- The reservation duration and retry policy for payment attempts (recommended: configurable, not hard-coded throughout controllers).
- The exact Midtrans QRIS flow (Snap or Core API) after reviewing current provider documentation.

Do not add reviews, wishlists, vouchers, shipping integrations, product gallery tables, or 3D model tables to the academic MVP unless explicitly approved later.
