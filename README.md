# Multi-Tenant Inventory Management System

A Laravel 13 inventory management API using a shared MySQL database. I built this project to manage stock across multiple tenants and warehouses while keeping inventory updates consistent under concurrent requests.

## Requirements

- PHP and Composer compatible with the project's `composer.json`
- MySQL with InnoDB tables
- Laravel 13

## Setup

```bash
composer install
```

Create the environment file and application key:

**Windows (PowerShell):**

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

**macOS / Linux:**

```bash
cp .env.example .env
php artisan key:generate
```

Set the database connection in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_db
DB_USERNAME=root
DB_PASSWORD=
```

Create the configured database, then run:

```bash
php artisan migrate
php artisan db:seed
php artisan serve
```

The database seeder provides sample tenants, products, warehouses, and stock records for development. Check the configured seeder before using `db:seed` if the project's default seeder has been customized.

## API

I expose three endpoints:

| Method | Endpoint | Purpose |
| --- | --- | --- |
| POST | `/api/stock-movements` | Record stock in, stock out, or a warehouse transfer |
| GET | `/api/stock-levels` | List stock levels with optional `product_id` and `warehouse_id` filters and pagination |
| GET | `/api/products/{sku}/history` | Get paginated stock movement history for a SKU in the current tenant |

The API uses Form Request validation and API Resources. Stock changes are handled by `InventoryService`, not by controllers.

**Example: stock out**

```http
POST /api/stock-movements
Accept: application/json
Content-Type: application/json
X-Tenant-ID: 1
```

```json
{
  "type": "out",
  "product_id": 1,
  "warehouse_id": 1,
  "quantity": 5,
  "reference": "ORDER-1001"
}
```

**Example: transfer**

```json
{
  "type": "transfer",
  "product_id": 1,
  "from_warehouse_id": 1,
  "to_warehouse_id": 2,
  "quantity": 5,
  "reference": "TRANSFER-1001"
}
```

**Example: paginated listing**

```http
GET /api/stock-levels?product_id=1&warehouse_id=1&per_page=20&page=1
X-Tenant-ID: 1

GET /api/products/PRODUCT-SKU/history?per_page=20&page=1
X-Tenant-ID: 1
```

**Required tenant header:** Every API request must include `X-Tenant-ID` with the tenant ID, including both GET endpoints and the POST endpoint. I use the tenant middleware to read this header and set the active tenant through `TenantGlobalService`. A request without a valid tenant header is rejected by the middleware.

Example request headers:

```http
Accept: application/json
Content-Type: application/json
X-Tenant-ID: 1
```

The examples use tenant `1`; replace it with the tenant ID configured in the database.

## Architecture and Design Decisions

### Multi-tenancy

I use a **shared database and shared schema**. Tenant-owned records have a `tenant_id`. Eloquent models use a `BelongsToTenant` trait / global scope to restrict normal model queries to the tenant selected by the middleware.

I also validate tenant ownership of referenced products and warehouses at the API boundary. This matters because table-based validation rules such as `Rule::exists('warehouses', 'id')` do **not** automatically apply Eloquent global scopes. Database constraints provide an additional layer of protection.

The tenant context is set explicitly for CLI tasks, including each concurrent test process, because CLI processes do not share the HTTP request's tenant context.

### Inventory operations and concurrency

I keep stock mutations in `InventoryService`:

- **StockIn:** add to an existing stock level, or create it if missing.
- **StockOut:** reject a withdrawal if the available quantity is insufficient.
- **Transfer:** subtract from the source warehouse, add to the destination warehouse, and record the transfer.

I use `DB::transaction()` for these operations and `lockForUpdate()` when reading the rows that are about to change. This serializes conflicting updates and prevents two withdrawals from both spending the same available stock. Stock creation is protected by a product-level lock in the current implementation, together with the unique stock-level key.

Transfers run in an outer database transaction. My current implementation reuses the stock-in and stock-out methods and suppresses their separate movement records during a transfer. It then writes **one** movement with `type = transfer` and `meta.from_warehouse_id` / `meta.to_warehouse_id`. If any step throws an exception, the enclosing transaction is rolled back.

I use a consistent product-first locking convention for the inventory methods. This simplifies correctness, although a product-level lock can reduce throughput when the same product is updated in many warehouses. The transaction and lock strategy is intended for MySQL/InnoDB; concurrency results may differ on other database engines.

### Inventory history

`stock_levels` stores the current stock quantity for each tenant/product/warehouse combination. `stock_movements` stores the history of stock changes. For transfer movements, the source and destination warehouse IDs are stored in `meta` so the movement can be interpreted without recording duplicate in/out movements for the same transfer.

## Indexing Strategy

I use tenant-prefixed composite indexes and uniqueness constraints for data isolation and efficient lookups:

| Table | Key / index | Reason |
| --- | --- | --- |
| `products` | `UNIQUE (tenant_id, sku)` | Keep SKUs unique within each tenant and support SKU lookup |
| `products` | `(tenant_id, id)` | Support tenant-aware composite references |
| `warehouses` | `(tenant_id, id)` | Support tenant-aware composite references |
| `stock_levels` | `UNIQUE (tenant_id, product_id, warehouse_id)` | Prevent duplicate stock rows and support stock lookups |
| `stock_levels` | `(tenant_id, warehouse_id, product_id)` | Support warehouse-focused inventory filtering |
| `stock_movements` | `(tenant_id, product_id, created_at, id)` | Support tenant-scoped product history queries |

Composite foreign keys help prevent tenant-owned records from pointing at a product or warehouse in another tenant. Primary keys and supporting indexes are created by the migrations as appropriate.

## Concurrency Test

I test withdrawals by creating a dedicated test product with **50 units** in one warehouse and running **100 one-unit withdrawals** using Laravel's `Concurrency` process driver.

```bash
php artisan inventory:test 1
```

`1` is the tenant ID. Use a tenant that has at least one warehouse. The test creates and mutates database records, so **run it against a development/test database, not production**.

The expected result is:

```text
Success: 50
Failed: 50
```

Each concurrent task resolves the tenant context in its own process before calling `InventoryService::StockOut`. Exactly 50 withdrawals should succeed; the remaining 50 should be rejected for insufficient stock.

The test depends on the installed Laravel concurrency driver and the ability to launch separate PHP processes. Check the Artisan command's actual output for the observed result; the values above are the acceptance criteria, not a claimed benchmark measurement.
