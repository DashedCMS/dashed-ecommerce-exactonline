<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExactProductIdToTabl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashed__product_exactonline', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained('dashed__products');
            $table->string('exactonline_id')->nullable();
            $table->string('error')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tabl', function (Blueprint $table) {
            //
        });
    }
}
