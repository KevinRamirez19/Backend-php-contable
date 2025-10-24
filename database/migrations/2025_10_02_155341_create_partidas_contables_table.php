<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partidas_contables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asiento_id')->constrained('asientos_contables')->onDelete('cascade');
            $table->foreignId('cuenta_id')->constrained('cuentas');
            $table->enum('tipo', ['debe','haber']);
    $table->decimal('monto', 15, 2);
    $table->string('descripcion')->nullable();
    $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partidas_contables');
    }
};