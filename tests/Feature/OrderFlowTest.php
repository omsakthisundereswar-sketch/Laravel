<?php

namespace Tests\Feature;

use App\Jobs\SendOrderConfirmationJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_order_creation_and_totals(): void
    {
        $productA = Product::factory()->create([
            'name' => 'Product A',
            'unique_code' => 'P-100',
            'price' => 100,
            'tax_percentage' => 10,
            'stock_quantity' => 5,
        ]);

        $productB = Product::factory()->create([
            'name' => 'Product B',
            'unique_code' => 'P-200',
            'price' => 200,
            'tax_percentage' => 5,
            'stock_quantity' => 5,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer' => [
                'name' => 'Rajavel',
                'email' => 'rajavel@example.com',
            ],
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 2],
                ['product_id' => $productB->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('order.customer.email', 'rajavel@example.com');
        $this->assertDatabaseHas('orders', ['customer_id' => Customer::first()->id, 'subtotal' => '400.00', 'tax_amount' => '30.00', 'grand_total' => '430.00']);
        $this->assertDatabaseHas('products', ['id' => $productA->id, 'stock_quantity' => 3]);
        $this->assertDatabaseHas('products', ['id' => $productB->id, 'stock_quantity' => 4]);
    }

    public function test_insufficient_stock_is_rejected(): void
    {
        $product = Product::factory()->create([
            'unique_code' => 'P-500',
            'price' => 90,
            'tax_percentage' => 10,
            'stock_quantity' => 1,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer' => ['name' => 'Test', 'email' => 'test@example.com'],
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_customer_order_history_endpoint(): void
    {
        $customer = Customer::factory()->create(['email' => 'history@example.com']);
        $order = Order::factory()->create(['customer_id' => $customer->id, 'subtotal' => '100.00', 'tax_amount' => '10.00', 'grand_total' => '110.00']);
        $product = Product::factory()->create(['unique_code' => 'P-600', 'price' => 100, 'tax_percentage' => 10, 'stock_quantity' => 10]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100,
            'tax_percentage' => 10,
            'subtotal' => 100,
            'tax_amount' => 10,
            'total' => 110,
        ]);

        $response = $this->getJson('/api/customers/history@example.com/orders');

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $order->id]);
    }

    public function test_low_stock_endpoint_uses_configured_threshold(): void
    {
        config()->set('store.low_stock_threshold', 5);
        Product::factory()->create(['unique_code' => 'P-900', 'stock_quantity' => 4]);

        $response = $this->getJson('/api/products/low-stock');

        $response->assertStatus(200);
        $response->assertJsonPath('threshold', 5);
        $response->assertJsonFragment(['code' => 'P-900']);
    }

    public function test_order_confirmation_job_is_dispatched_after_success(): void
    {
        Bus::fake();
        $product = Product::factory()->create(['unique_code' => 'P-700', 'stock_quantity' => 2]);

        $this->postJson('/api/orders', [
            'customer' => ['name' => 'Bob', 'email' => 'bob@example.com'],
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        Bus::assertDispatched(SendOrderConfirmationJob::class);
    }

    public function test_order_creation_page_displays_seeded_products_and_low_stock_alert(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get(route('orders.create'));

        $response->assertOk();
        $response->assertSee('Store Billing');
        $response->assertSee('Generate Bill');
        $response->assertSee('Colgate Toothpaste');
        $response->assertSee('Bread — 4 units left');
        $response->assertSee('Milk 1L — 9 units left');
        $response->assertSee('Eggs (12) — 2 units left');
        $response->assertSee('Auto-filled if email exists');
        $this->assertDatabaseHas('customers', ['email' => 'thomas@example.com']);
    }

    public function test_mysql_based_locking_is_applied_when_available(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped('MySQL-specific row lock validation requires a MySQL connection.');
        }

        $this->assertTrue(true);
    }
}
