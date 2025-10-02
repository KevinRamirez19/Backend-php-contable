<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->string('marca', 50);
            $table->string('modelo', 50);
            $table->year('año');
            $table->string('color', 30)->nullable();
            $table->string('placa', 15)->unique()->nullable();
            $table->string('vin', 17)->unique()->nullable();
            $table->decimal('precio_compra', 12, 2);
            $table->decimal('precio_venta', 12, 2);
            $table->enum('estado', ['DISPONIBLE', 'VENDIDO', 'MANTENIMIENTO'])->default('DISPONIBLE');
            $table->integer('stock')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};