<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class StatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('statuses')->insert([
            ['name' => 'Pending', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Packaging', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shipped', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Delivered', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cancel', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Return', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
