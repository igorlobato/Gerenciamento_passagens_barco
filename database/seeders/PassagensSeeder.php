<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PassagensSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('passagens')->insert([
            [
                'id_barco' => 1,
                'id_rota' => 1,
                'preco' => 150.00,
                'horario_dia_ida' => Carbon::now()->addDays(3)->setTime(8, 0),
                'horario_dia_volta' => Carbon::now()->addDays(5)->setTime(17, 30),
                'tempo de viagem' => '02:30:00',
            ],
            [
                'id_barco' => 2,
                'id_rota' => 2,
                'preco' => 200.00,
                'horario_dia_ida' => Carbon::now()->addDays(2)->setTime(7, 0),
                'horario_dia_volta' => Carbon::now()->addDays(4)->setTime(18, 0),
                'tempo de viagem' => '03:00:00',
            ],
        ]);
    }
}
