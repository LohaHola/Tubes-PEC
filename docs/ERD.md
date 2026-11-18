# Tubes-PEC ERD v1.0

Approved conceptual entity relationship diagram. The complete data dictionary, constraints, lifecycle rules, and implementation notes are in [DATABASE.md](DATABASE.md).

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
        varchar role
    }
    CATEGORIES {
        bigint id PK
        varchar name
        varchar slug UK
        boolean is_active
    }
    PRODUCTS {
        bigint id PK
        bigint category_id FK
        varchar sku UK
        varchar name
        decimal price
        unsigned_int stock
        json specifications
        varchar image_path
    }
    CARTS {
        bigint id PK
        bigint user_id FK_UK
    }
    CART_ITEMS {
        bigint id PK
        bigint cart_id FK
        bigint product_id FK
        unsigned_int quantity
    }
    ORDERS {
        bigint id PK
        bigint user_id FK
        varchar order_number UK
        decimal subtotal
        decimal shipping_cost
        decimal total_amount
        varchar payment_status
        varchar fulfillment_status
        varchar inventory_status
    }
    ORDER_ITEMS {
        bigint id PK
        bigint order_id FK
        bigint product_id FK_NULL
        varchar product_name
        decimal unit_price
        unsigned_int quantity
        decimal line_total
    }
    PAYMENTS {
        bigint id PK
        bigint order_id FK
        varchar provider_order_id UK
        varchar provider_transaction_id UK_NULL
        decimal amount
        varchar status
    }
    PAYMENT_NOTIFICATIONS {
        bigint id PK
        bigint payment_id FK
        char notification_key UK
        varchar transaction_status
        varchar processing_status
    }
```
