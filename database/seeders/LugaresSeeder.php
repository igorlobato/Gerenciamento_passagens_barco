<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LugaresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lugares')->insert([
            ['nome' => 'Ilha do Sol', 'foto' => 'ilha_sol.jpg'],
            ['nome' => 'Porto Azul', 'foto' => 'porto_azul.jpg'],
            ['nome' => 'Praia Vermelha', 'foto' => 'praia_vermelha.jpg'],
        ]);
    }
}
