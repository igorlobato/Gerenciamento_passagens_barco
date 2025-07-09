<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RotasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rotas')->insert([
            [
                'nome' => 'Rota 1',
                'descricao_rota' => 'Viagem tranquila pela costa.',
                'id_origem' => 1,
                'id_destino' => 2,
            ],
            [
                'nome' => 'Rota 2',
                'descricao_rota' => 'Passagem por águas profundas.',
                'id_origem' => 2,
                'id_destino' => 3,
            ],
        ]);
    }
}
