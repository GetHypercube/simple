<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnumAnonimoToAccesoModoOnTarea extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('tarea', function (Blueprint $table) {
            $table->dropColumn('acceso_modo');
        });
        
        Schema::table('tarea', function (Blueprint $table) {

            $table->enum('acceso_modo', ['grupos_usuarios', 'publico', 'registrados', 'claveunica', 'anonimo'])->default('grupos_usuarios')->after('almacenar_usuario_variable');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::table('tarea', function (Blueprint $table) {
            $table->dropColumn('acceso_modo');
        });
        Schema::table('tarea', function (Blueprint $table) {
            $table->enum('acceso_modo', ['grupos_usuarios', 'publico', 'registrados', 'claveunica'])->default('grupos_usuarios')->after('almacenar_usuario_variable');
        });
        
    }
}
