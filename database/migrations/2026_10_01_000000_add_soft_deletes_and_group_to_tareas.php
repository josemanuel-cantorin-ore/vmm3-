<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            if (! Schema::hasColumn('tareas', 'grupo_tarea')) {
                $table->string('grupo_tarea', 36)->nullable()->index();
            }

            if (! Schema::hasColumn('tareas', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            if (Schema::hasColumn('tareas', 'grupo_tarea')) {
                $table->dropIndex(['grupo_tarea']);
                $table->dropColumn('grupo_tarea');
            }

            if (Schema::hasColumn('tareas', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
