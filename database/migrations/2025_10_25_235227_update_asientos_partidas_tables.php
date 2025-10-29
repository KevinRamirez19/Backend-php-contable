<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 🧾 Ajustar la tabla asientos_contables (si falta algo)
        Schema::table('asientos_contables', function (Blueprint $table) {
            if (!Schema::hasColumn('asientos_contables', 'descripcion')) {
                $table->string('descripcion')->nullable();
            }
        });

        // 📘 Ajustar la tabla partidas_contables
        Schema::table('partidas_contables', function (Blueprint $table) {
            // Si las columnas no existen, se crean
            if (!Schema::hasColumn('partidas_contables', 'asiento_id')) {
                $table->unsignedBigInteger('asiento_id')->nullable();
            }
            if (!Schema::hasColumn('partidas_contables', 'cuenta_id')) {
                $table->unsignedBigInteger('cuenta_id')->nullable();
            }
            if (!Schema::hasColumn('partidas_contables', 'debe')) {
                $table->decimal('debe', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('partidas_contables', 'haber')) {
                $table->decimal('haber', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('partidas_contables', 'descripcion')) {
                $table->string('descripcion')->nullable();
            }

            // 🔗 Relaciones
            $table->foreign('asiento_id')
                  ->references('id')
                  ->on('asientos_contables')
                  ->onDelete('cascade');

            $table->foreign('cuenta_id')
                  ->references('id')
                  ->on('cuentas');
        });
    }

    public function down(): void
    {
        Schema::table('partidas_contables', function (Blueprint $table) {
            $table->dropForeign(['asiento_id']);
            $table->dropForeign(['cuenta_id']);
        });
    }
};
