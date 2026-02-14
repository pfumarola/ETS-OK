<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Contanti', 'Bonifico', 'Carta', 'Altro'] as $name) {
            PaymentMethod::firstOrCreate(['name' => $name]);
        }
    }
}
