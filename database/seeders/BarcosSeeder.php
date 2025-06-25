<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarcosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('barcos')->insert([
            ['nome' => 'Barco Águia'],
            ['nome' => 'Barco Vento Norte'],
            ['nome' => 'Barco Estrela do Mar'],
        ]);
    }
}
