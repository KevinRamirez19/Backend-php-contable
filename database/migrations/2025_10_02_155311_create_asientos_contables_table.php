<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asientos_contables', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->text('descripcion');
            $table->date('fecha');
             $table->decimal('total', 15, 2)->default(0);
            $table->foreignId('compra_id')->nullable()->constrained('compras');
            $table->foreignId('venta_id')->nullable()->constrained('ventas');
            $table->foreignId('created_by')->constrained('usuarios');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asientos_contables');
    }
};