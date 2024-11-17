<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryCompaniesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('delivery_companies')->insert([
            ['name' => 'Steadfast', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pathao', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'N/A', 'created_at' => now(), 'updated_at' => now()]
        ]);
    }
}
