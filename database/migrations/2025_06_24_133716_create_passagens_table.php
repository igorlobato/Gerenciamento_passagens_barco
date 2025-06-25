<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('passagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_barco')->constrained('barcos')->nullOnDelete('cascade');
            $table->foreignId('id_rota')->constrained('rotas')->nullOnDelete();
            $table->float('preco');
            $table->dateTime('horario_dia_ida');
            $table->dateTime('horario_dia_volta');
            $table->time('tempo de viagem');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passagens');
    }
};
