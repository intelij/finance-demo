<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCsvDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create ( 'csvData', function ($table) {
            $table->integer ( 'id' );
            $table->string ( 'firstname' );
            $table->string ( 'lastname' );
            $table->string ( 'email' );
            $table->string ( 'gender' );
        } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
