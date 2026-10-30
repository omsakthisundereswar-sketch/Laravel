# Store Order & Inventory Mini-System

A Laravel mini-system for creating retail orders, tracking inventory, customer history, and low-stock reporting.

The original development master prompt is available in [prompts/MASTER_PROMPT.md](prompts/MASTER_PROMPT.md).

## Tech stack

- PHP 8.5
- Laravel 13
- SQLite by default; MySQL is also supported for local development
- MySQL-compatible migrations and transactional inventory updates

## Local setup

Requirements: PHP 8.3 or later, Composer, and Node.js/npm.

Install dependencies and create your local environment:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

The default `.env.example` uses SQLite. Ensure the SQLite database file exists, then create the tables and seed the sample products and customers:

```bash
touch database/database.sqlite
php artisan migrate --seed
```

If you prefer MySQL, create a database first:

```sql
CREATE DATABASE store_order_inventory;
```

Then set these values in `.env` using your local MySQL credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=store_order_inventory
DB_USERNAME=root
DB_PASSWORD=your-local-password
```

Run `php artisan migrate --seed` after changing the connection. Do not commit `.env` or real credentials.

Build the frontend assets and start the application:

```bash
npm install
npm run build
php artisan serve
```

Open `http://localhost:8000`.

### Database commands

```bash
php artisan migrate:status  # Check which migrations have run
php artisan db:seed         # Seed sample products and customers
```

To completely rebuild the database and reseed it, run `php artisan migrate:fresh --seed`. **This deletes all existing tables and data.**

## Rounding approach

All order totals are calculated in PHP using `round($value, 2)` to avoid floating-point drift and keep money values consistent. The database stores values as `DECIMAL(12,2)` and `DECIMAL(10,2)` so monetary amounts remain stable.

## Low-stock configuration

The threshold is defined in `config/store.php` and defaults to 10 units. The sample inventory includes Bread (4), Milk 1L (9), and Eggs (12) (2), so these appear in the order screen's low-stock alert:

```php
return [
    'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 10),
];
```

To override it in `.env`:

```env
LOW_STOCK_THRESHOLD=5
```

Seed the sample catalog and customers without resetting existing product stock:

```bash
php artisan migrate --seed
```

The sample products are Colgate Toothpaste, Parle-G Biscuit, Bread, Milk 1L, and Eggs (12). Sample customers include `thomas@example.com`, `rajavel@example.com`, and `anitha@example.com`; entering a known email on the order form fills in the customer's name.

## Queue setup

Use the database queue driver in `.env`:

```env
QUEUE_CONNECTION=database
```

The `jobs` table is included in the application's migrations. Run `php artisan migrate` if you have not run the migrations yet.

Start the queue worker:

```bash
php artisan queue:work --tries=3
```

To run the worker in the foreground while testing:

```bash
php artisan queue:listen --tries=3
```

## Race conditions and inventory protection

A race condition happens when two requests read the same stock value before either saves its update. Without a lock, both can pass the availability check and oversell the same unit. The order service prevents this by running the order creation and stock deduction inside a database transaction and locking the affected product rows with `lockForUpdate()` when the connection is MySQL-backed.

This is why the `orders` flow wraps validation, inventory deduction, and order insertion inside `DB::transaction()`. If one request wins the lock, the second request must wait until the first finishes and then re-check stock before it can continue.

A practical concurrency check for MySQL is to run two simultaneous API requests against a product with stock quantity 1 and confirm that only one order succeeds. Example:

```bash
curl -sS -X POST http://localhost:8000/api/orders -H 'Content-Type: application/json' \
  -d '{"customer":{"name":"A","email":"a@example.com"},"items":[{"product_id":1,"quantity":1}]}' &
curl -sS -X POST http://localhost:8000/api/orders -H 'Content-Type: application/json' \
  -d '{"customer":{"name":"B","email":"b@example.com"},"items":[{"product_id":1,"quantity":1}]}'
```

## Typical usage

```bash
php artisan migrate --seed
php artisan serve
```

After setup, open the application at `http://localhost:8000` and create products, customers, and orders.

## Push changes to Git

Check your current branch and working tree, then stage and commit the files you intend to share:

```bash
git status
git add README.md
git commit -m "docs: explain setup and database commands"
git push -u origin <branch-name>
```

Replace `<branch-name>` with your current branch name (for example, `main` or a feature branch). If the repository has no `origin` remote yet, add the repository URL first with `git remote add origin <repository-url>`. Never stage or push `.env` or other secrets.

## API examples

Create order:

```bash
curl -X POST http://localhost:8000/api/orders \
  -H 'Content-Type: application/json' \
  -d '{
    "customer": {"name":"Rajavel","email":"rajavel@example.com"},
    "items": [{"product_id":1,"quantity":2},{"product_id":2,"quantity":1}]
  }'
```

Customer order history:

```bash
curl http://localhost:8000/api/customers/rajavel@example.com/orders
```

Low stock products:

```bash
curl http://localhost:8000/api/products/low-stock
```
