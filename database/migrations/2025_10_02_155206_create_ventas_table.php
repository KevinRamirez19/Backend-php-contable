<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('numero_factura', 50)->unique()->nullable();
            $table->date('fecha_venta');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('iva', 12, 2);
            $table->decimal('total', 12, 2);
            $table->enum('estado_dian', ['PENDIENTE', 'ACEPTADA', 'RECHAZADA', 'ENVIADA'])->default('PENDIENTE');
            $table->string('cufe', 200)->nullable();
            $table->text('qr_code')->nullable();
            $table->foreignId('created_by')->constrained('usuarios');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};