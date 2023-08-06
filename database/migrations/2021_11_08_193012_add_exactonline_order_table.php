<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExactonlineOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashed__order_exactonline', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('dashed__orders');
            $table->string('exactonline_id')->nullable();
            $table->string('error')->nullable();
            $table->tinyInteger('pushed')->default(0);

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
        //
    }
}
