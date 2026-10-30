<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Thomas Mathew', 'email' => 'thomas@example.com'],
            ['name' => 'Rajavel Kumar', 'email' => 'rajavel@example.com'],
            ['name' => 'Anitha Devi', 'email' => 'anitha@example.com'],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['email' => $customer['email']],
                $customer
            );
        }
    }
}
