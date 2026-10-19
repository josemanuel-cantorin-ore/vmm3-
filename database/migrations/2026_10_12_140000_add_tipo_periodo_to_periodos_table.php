<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('periodos', 'tipo_periodo')) {
            Schema::table('periodos', function (Blueprint $table) {
                $table->string('tipo_periodo', 20)->default('bimestre');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('periodos', 'tipo_periodo')) {
            Schema::table('periodos', function (Blueprint $table) {
                $table->dropColumn('tipo_periodo');
            });
        }
    }
};
