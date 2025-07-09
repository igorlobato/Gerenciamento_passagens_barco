<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ViagensSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('viagens')->insert([
            ['id_passagem' => 3, 'status' => 'Concluida'],
            ['id_passagem' => 4, 'status' => 'Em andamento'],
        ]);
    }
}
