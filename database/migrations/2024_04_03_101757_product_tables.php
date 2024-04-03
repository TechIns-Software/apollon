<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('product');
        Schema::create('product', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();

            $table->bigInteger('business_id')->unsigned();
            $table->foreign('business_id')->references('id')
                ->on('business')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        Schema::dropIfExists('product_order');

        Schema::create('product_order',function (Blueprint $table){
            $table->bigInteger('product_id')->unsigned();
            $table->foreign('product_id')->references('id')
                ->on('product')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->bigInteger('order_id')->unsigned();
            $table->foreign('order_id')->references('id')
                ->on('order')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->float('ammount');

            $table->primary(['product_id', 'order_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_order');
        Schema::dropIfExists('product');
    }
};
