<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 🧾 Ajustar la tabla asientos_contables (si falta algo)
        if (Schema::hasTable('asientos_contables')) {
            Schema::table('asientos_contables', function (Blueprint $table) {
                if (!Schema::hasColumn('asientos_contables', 'descripcion')) {
                    $table->string('descripcion')->nullable();
                }
            });
        }

        // 📘 Ajustar la tabla partidas_contables
        if (Schema::hasTable('partidas_contables')) {
            Schema::table('partidas_contables', function (Blueprint $table) {
                // ⚠️ Primero eliminar claves foráneas duplicadas si existen
                try {
                    DB::statement('ALTER TABLE partidas_contables DROP FOREIGN KEY partidas_contables_asiento_id_foreign;');
                } catch (\Exception $e) {
                    // Si no existe la clave, no hace nada
                }
                try {
                    DB::statement('ALTER TABLE partidas_contables DROP FOREIGN KEY partidas_contables_cuenta_id_foreign;');
                } catch (\Exception $e) {
                    // Si no existe la clave, no hace nada
                }

                // 🧱 Asegurar que las columnas existen
                if (!Schema::hasColumn('partidas_contables', 'asiento_id')) {
                    $table->unsignedBigInteger('asiento_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('partidas_contables', 'cuenta_id')) {
                    $table->unsignedBigInteger('cuenta_id')->nullable()->after('asiento_id');
                }
                if (!Schema::hasColumn('partidas_contables', 'debe')) {
                    $table->decimal('debe', 15, 2)->default(0)->after('cuenta_id');
                }
                if (!Schema::hasColumn('partidas_contables', 'haber')) {
                    $table->decimal('haber', 15, 2)->default(0)->after('debe');
                }
                if (!Schema::hasColumn('partidas_contables', 'descripcion')) {
                    $table->string('descripcion')->nullable()->after('haber');
                }

                // 🔗 Relaciones (solo si las tablas de referencia existen)
                if (Schema::hasTable('asientos_contables')) {
                    $table->foreign('asiento_id')
                        ->references('id')
                        ->on('asientos_contables')
                        ->onDelete('cascade');
                }

                if (Schema::hasTable('cuentas')) {
                    $table->foreign('cuenta_id')
                        ->references('id')
                        ->on('cuentas');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('partidas_contables')) {
            Schema::table('partidas_contables', function (Blueprint $table) {
                if (Schema::hasColumn('partidas_contables', 'asiento_id')) {
                    $table->dropForeign(['asiento_id']);
                }
                if (Schema::hasColumn('partidas_contables', 'cuenta_id')) {
                    $table->dropForeign(['cuenta_id']);
                }
            });
        }
    }
};
