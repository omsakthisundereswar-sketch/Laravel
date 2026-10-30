# MASTER PROMPT – Laravel Developer Mini Task


Act as a Senior Laravel Developer, Backend Architect, Database Designer, and Software Tester.

I need you to develop a complete, production-quality Laravel application based on the requirements in the Mallow Technologies Laravel Developer Mini Task PDF.

The application name is:

**Store Order & Inventory Mini-System**

Your responsibility is to analyze the requirements, design the application, implement all features, write automated tests, document the project, and prepare it for GitHub submission.

Do not just provide sample code or explanations. Implement the complete working application in the project directory.

---

# 1. Understand the Requirements

Build a small retail billing and inventory management system.

The application must support:

1. Product management.
2. Customer management.
3. Customer order creation.
4. Multiple products in one order.
5. Automatic subtotal calculation.
6. Automatic tax calculation.
7. Grand total calculation.
8. Automatic stock deduction.
9. Customer order history.
10. Low-stock product alerts.
11. Queued order-confirmation email simulation.
12. Concurrency-safe inventory management.
13. Automated tests.
14. Proper README documentation.

Follow the assignment requirements carefully. Do not introduce unnecessary features that increase the development scope.

# 2. Technology Stack

Use the following technology stack:

- Backend: Laravel
- Language: PHP
- Database: MySQL
- ORM: Laravel Eloquent
- API: RESTful JSON API
- Authentication: Not required unless the existing project already requires it.
- Validation: Laravel Form Request Validation
- Background Processing: Laravel Queue
- Testing: PHPUnit or Pest
- Database Seeding: Factories and Seeders
- Version Control: Git

First inspect the existing project directory.

Before making changes:

- Check the installed Laravel version.
- Check the PHP version.
- Check the database configuration.
- Inspect existing dependencies.
- Identify whether the project is new or an existing Laravel application.

Do not unnecessarily upgrade Laravel or replace existing dependencies.

Use versions compatible with the existing application.

# 3. Database Design

Create a normalized relational database schema using Laravel migrations.

## Products Table

Fields:

- id
- name
- code (unique)
- price (decimal)
- tax_percentage (decimal)
- stock_quantity (integer)
- timestamps

Requirements:

- Product code must be unique.
- Price must be greater than or equal to zero.
- Tax percentage must be valid and non-negative.
- Stock quantity cannot be negative.

## Customers Table

Fields:

- id
- name
- email (unique)
- timestamps

Requirements:

- Email must be unique.
- Validate email format.
- Customer details should be reused when an existing email is provided.

## Orders Table

Fields:

- id
- customer_id (foreign key)
- subtotal (decimal)
- tax_amount (decimal)
- grand_total (decimal)
- timestamps

Requirements:

- Every order belongs to one customer.
- Every order must have at least one product.
- Store calculated order totals.

## Order Items Table

Fields:

- id
- order_id (foreign key)
- product_id (foreign key)
- quantity
- unit_price
- tax_percentage
- subtotal
- tax_amount
- total
- timestamps

Requirements:

- An order can contain multiple products.
- Store the product price and tax percentage at the time of ordering.
- Historical order details must not change when the product price or tax percentage changes later.
- Use appropriate foreign keys and indexes.

Use appropriate decimal precision for monetary values.

Document any database design assumptions in README.md.

# 4. Eloquent Relationships

Implement proper Eloquent relationships.

Product:
- Has many order items.

Customer:
- Has many orders.

Order:
- Belongs to customer.
- Has many order items.

OrderItem:
- Belongs to order.
- Belongs to product.

Use eager loading to avoid N+1 query problems.

# 5. API Development

Create clean RESTful API endpoints.

Use API routes in routes/api.php.

## API 1: Create Order

Endpoint:

POST /api/orders

Example request:

{
  "customer": {
    "name": "John Thomas",
    "email": "john@example.com"
  },
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 2,
      "quantity": 3
    }
  ]
}

Requirements:

1. Validate customer name and email.
2. Check whether the customer already exists using email.
3. Reuse an existing customer if the email exists.
4. Otherwise create a new customer.
5. Validate that items is an array containing at least one product.
6. Validate product IDs.
7. Validate quantities as positive integers.
8. Check product availability.
9. Calculate each order item's subtotal.
10. Calculate tax using the product's tax percentage.
11. Calculate the order subtotal.
12. Calculate the total tax.
13. Calculate the grand total.
14. Save the order and order items.
15. Deduct the purchased quantities from inventory.
16. Dispatch the order-confirmation queued job.
17. Return the complete created order as JSON.

Calculation example:

Product A:
- Price: 100
- Tax: 10%
- Quantity: 2

Subtotal = 200

Tax = 20

Grand Total = 220

Calculate all monetary values on the backend.

Never trust totals submitted by the frontend.

Use consistent monetary rounding to two decimal places.

The entire order creation process must be transactional.

If any operation fails, roll back the entire transaction.

## API 2: Customer Order History

Endpoint:

GET /api/customers/{email}/orders

Requirements:

- Find the customer using email.
- Return all orders belonging to that customer.
- Include order items.
- Include product details where appropriate.
- Include subtotal, tax, and grand total.
- Return a proper 404 response if the customer does not exist.
- Use pagination.
- Avoid N+1 queries.

## API 3: Low Stock Products

Endpoint:

GET /api/products/low-stock

Requirements:

- Return products whose stock quantity is below a configurable threshold.
- Store the default threshold in Laravel configuration.
- Allow the threshold to be overridden through a validated query parameter.
- Return product name, code, and available stock.
- Return an empty array when no products match.

Example:

GET /api/products/low-stock?threshold=5

Do not hardcode the threshold throughout the application.

# 6. Concurrency and Inventory Safety

This is one of the most important requirements.

Implement proper concurrency protection to prevent overselling.

Example:

Product stock = 1.

Two customers simultaneously place orders for quantity 1.

Expected result:

- First order succeeds.
- Second order fails with a clean insufficient-stock response.
- Stock must never become negative.
- Only one order should be created.

Use:

- DB transactions.
- Database row-level locking using lockForUpdate().
- Proper transaction boundaries.
- Recheck stock inside the transaction.
- Lock product rows in a consistent order when multiple products are involved.

Do not perform the stock check outside the transaction and assume the result is still valid.

Ensure that the stock deduction and order creation happen atomically.

Handle concurrency-related failures cleanly.

Document the database engine and transaction assumptions in README.md.

# 7. Business Logic / Service Layer

Keep controllers thin.

Create a dedicated service or action class for order creation.

Suggested structure:

app/
  Http/
    Controllers/
      Api/
        OrderController.php
        CustomerOrderController.php
        ProductController.php

    Requests/
      StoreOrderRequest.php

    Resources/
      OrderResource.php
      OrderItemResource.php
      ProductResource.php

  Models/
    Product.php
    Customer.php
    Order.php
    OrderItem.php

  Services/
    OrderService.php

  Jobs/
    SendOrderConfirmationJob.php

You may improve this structure if there is a good architectural reason.

Keep responsibilities separate:

- Controllers: Handle HTTP requests and responses.
- Form Requests: Validate incoming data.
- Services: Handle business logic.
- Models: Handle relationships.
- Resources: Format API responses.
- Jobs: Handle asynchronous processing.

Avoid putting all business logic inside controllers or models.

# 8. Queued Job – Order Confirmation

After successful order creation, dispatch:

SendOrderConfirmationJob

Requirements:

- The job should receive the created order ID.
- Load the order and customer information.
- Simulate sending an order-confirmation email.
- Logging the email details is acceptable.
- A real SMTP configuration is not required.
- Use Laravel's queue system.
- Do not execute the email simulation directly inside the controller.
- Dispatch the job only after the order transaction commits.
- Handle job failures appropriately.

Use Laravel's database queue driver for easy local testing, if compatible with the installed Laravel version.

Document how to run the queue worker:

php artisan queue:work

# 9. User Interface – Billing Wireframe

Implement a simple and clean billing interface based on the PDF wireframe, if a UI is included in the project scope.

The wireframe contains these sections:

## Customer Section

- Customer email.
- Customer name.
- Automatically populate the name when an existing email is identified, where practical.

## Products Section

- Product selection.
- Quantity.
- Unit price.
- Line total.
- Add Product button.
- Support multiple product rows.

## Low Stock Alert

Display products with low inventory.

Show:
- Product name.
- Remaining stock quantity.

## Payment Section

Display:
- Subtotal.
- Tax.
- Grand Total.

The wireframe also illustrates:
- Amount given by customer.
- Balance to return.
- Generate Bill button.

These payment and cash-change elements are visual reference details. Do not introduce a separate payment-processing system unless needed and clearly documented.

## Generate Bill

- Submit the order through the order creation API.
- Display the generated order details.
- Show product lines and calculated totals.
- Display a success or error message.

The backend must remain responsible for all calculations and inventory updates.

Do not implement real SMTP or PDF generation unless explicitly needed; the assignment only requires simulated queued email processing.

UI styling should be simple, professional, responsive, and easy to use.

Do not spend excessive time on visual styling.

# 10. Database Factories and Seeders

Create factories and seeders for:

- Products.
- Customers.

Add realistic sample records.

Suggested products:

1. Colgate Toothpaste
2. Parle-G Biscuit
3. Bread
4. Milk
5. Eggs

Create sample customers.

Requirements:

- Product codes must be unique.
- Product prices should be realistic.
- Tax percentages should contain sample values.
- Stock quantities should vary.
- Seeders must be safe to run in a fresh development database.

Provide:

php artisan migrate --seed

# 11. Automated Testing

Write meaningful feature and unit tests using PHPUnit or Pest.

At minimum, cover the following scenarios.

## Test 1: Successful Order Creation

- Submit valid customer details.
- Submit multiple products.
- Verify order creation.
- Verify order items.
- Verify subtotal.
- Verify tax.
- Verify grand total.
- Verify stock deduction.
- Verify queued job dispatch.

## Test 2: Insufficient Stock

- Create a product with stock quantity 1.
- Attempt to order quantity 2.
- Expect a clean validation/business error.
- Verify no order is created.
- Verify stock remains unchanged.

## Test 3: Customer Reuse

- Create a customer.
- Place another order using the same email.
- Verify the existing customer is reused.
- Verify no duplicate customer is created.

## Test 4: Invalid Product

- Submit an invalid product ID.
- Verify the API returns an appropriate error.
- Verify no order is created.

## Test 5: Invalid Quantity

Test:
- Zero quantity.
- Negative quantity.
- Missing quantity.
- Invalid quantity type.

## Test 6: Multiple Products

Verify that totals and stock deductions are correct when an order contains multiple products.

## Test 7: Customer Order History

- Verify customer order history.
- Verify orders and order items.
- Verify unknown customer returns 404.

## Test 8: Low Stock API

- Verify products below the default threshold.
- Verify custom threshold.
- Verify products outside the threshold are excluded.

## Test 9: Transaction Rollback

Simulate a failure during order processing.

Verify:
- No partial order remains.
- No partial order items remain.
- Stock is not incorrectly deducted.

## Test 10: Concurrent Orders

Where practical, create a concurrency integration test using separate database connections or processes.

Verify that two competing orders for the last available unit cannot both succeed.

Do not claim concurrency is tested merely because lockForUpdate() exists in the code.

Use the actual supported database engine for concurrency integration testing.

Ensure all tests pass.

# 12. API Response Standards

Use consistent JSON responses.

Successful response example:

{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 1,
    "customer": {
      "name": "John Thomas",
      "email": "john@example.com"
    },
    "subtotal": 200.00,
    "tax_amount": 20.00,
    "grand_total": 220.00,
    "items": []
  }
}

Error response example:

{
  "success": false,
  "message": "Insufficient stock for Colgate Toothpaste",
  "errors": {
    "items": [
      "Available stock is 1, requested quantity is 2."
    ]
  }
}

Use appropriate HTTP status codes:

- 200: Successful retrieval.
- 201: Successful order creation.
- 404: Resource not found.
- 422: Validation or insufficient-stock failure.
- 500: Unexpected server error.

Do not expose database errors, stack traces, or sensitive internal information in API responses.

# 13. README.md Documentation

Create a professional README.md.

Include:

1. Project title.
2. Project overview.
3. Features.
4. Technology stack.
5. Prerequisites.
6. Installation instructions.
7. Environment configuration.
8. Database setup.
9. Migration and seeding commands.
10. Queue configuration.
11. Queue worker instructions.
12. API endpoint documentation.
13. Example request and response.
14. Database schema overview.
15. Business logic explanation.
16. Inventory concurrency protection explanation.
17. Automated test instructions.
18. Assumptions and design decisions.
19. Known limitations.
20. Screenshots or UI instructions, if applicable.

Installation example:

composer install

cp .env.example .env

php artisan key:generate

php artisan migrate --seed

php artisan serve

php artisan queue:work

Document any changes required for the actual installed Laravel version.

# 14. Prompt Log Requirement

The assignment explicitly asks for screenshots of actual AI prompts used during development.

Create a prompts/ directory.

Include:

- Requirements analysis prompt.
- Database design prompt.
- API development prompt.
- Order calculation and stock management prompt.
- Concurrency implementation prompt.
- Queue job implementation prompt.
- Testing prompt.
- Code review and optimization prompt.

Save actual prompt screenshots from the AI development tool.

Do not create fake screenshots or claim that screenshots exist when they do not.

# 15. Code Quality and Security

Follow Laravel coding standards.

Requirements:

- Use PSR-12.
- Use meaningful class and method names.
- Avoid duplicated code.
- Use dependency injection.
- Use proper validation.
- Protect against SQL injection through Eloquent and parameter binding.
- Use database transactions.
- Use appropriate indexes.
- Avoid N+1 queries.
- Use mass-assignment protection.
- Do not expose sensitive configuration.
- Do not hardcode database credentials.
- Handle exceptions appropriately.
- Keep code readable and maintainable.

Do not over-engineer the mini project.

# 16. GitHub Submission Preparation

Prepare the project for submission.

Ensure:

- All application code is included.
- Migrations and seeders are included.
- Tests are included.
- README.md is complete.
- prompts/ contains actual prompt screenshots.
- .env is excluded.
- vendor/ is excluded.
- No passwords or secrets are committed.
- .env.example is included.

Prepare a private GitHub repository.

Do not push code or make repository changes without my explicit approval.

# 17. Final Verification Checklist

Before declaring the task complete, verify every requirement.

[ ] Database migrations work.

[ ] Product and customer factories work.

[ ] Database seeders work.

[ ] Product and customer relationships work.

[ ] Order creation API works.

[ ] Existing customers are reused.

[ ] Multiple products per order work.

[ ] Subtotal calculation is correct.

[ ] Tax calculation is correct.

[ ] Grand total calculation is correct.

[ ] Stock deduction works.

[ ] Insufficient stock is handled correctly.

[ ] Transaction rollback works.

[ ] Concurrent requests cannot oversell stock.

[ ] Customer order history API works.

[ ] Low-stock API works.

[ ] Configurable stock threshold works.

[ ] Queued email job works.

[ ] Queue worker processes jobs.

[ ] Automated tests pass.

[ ] README is complete.

[ ] Actual AI prompt screenshots are prepared.

[ ] Application is ready for GitHub submission.

# 18. Development Instructions

Follow this implementation sequence:

Phase 1: Inspect the project and understand requirements.

Phase 2: Design database tables and relationships.

Phase 3: Implement migrations, models, factories, and seeders.

Phase 4: Implement API routes, requests, controllers, resources, and services.

Phase 5: Implement order calculations and transactional inventory deduction.

Phase 6: Implement concurrency protection.

Phase 7: Implement queued confirmation job.

Phase 8: Implement the low-stock and order-history APIs.

Phase 9: Implement the billing UI based on the wireframe, if applicable.

Phase 10: Implement automated tests.

Phase 11: Execute tests and fix all failures.

Phase 12: Review code quality and security.

Phase 13: Prepare README and prompt documentation.

Phase 14: Perform final requirement-by-requirement verification.

Important working rules:

1. Work directly in the existing project directory.
2. Do not overwrite existing working code without inspecting it.
3. Do not skip any mandatory requirement.
4. Do not create unnecessary files or dependencies.
5. Do not use fake implementations for required functionality.
6. Run the relevant tests after each major implementation phase.
7. If something is ambiguous, make a reasonable decision and document it in README.md.
8. Do not silently ignore errors.
9. Explain important architectural decisions briefly.
10. Ask me only when an essential decision cannot reasonably be made.
11. Never claim a feature or test is complete without actually verifying it.

Start by inspecting the project and reporting:

- Laravel version.
- PHP version.
- Database configuration.
- Existing project structure.
- Implementation plan.

Then proceed with implementation phase by phase.

**Final objective:** Deliver a fully functional, tested, maintainable Laravel Store Order & Inventory Mini-System that satisfies the Mallow Technologies take-home assignment and is ready for technical evaluation.
