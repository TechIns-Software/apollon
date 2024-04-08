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
        Schema::dropIfExists('driver');
        Schema::create('driver', function (Blueprint $table) {
            $table->id();
            $table->string('driver_name');

            $table->bigInteger('business_id')->unsigned();

            $table->foreign('business_id')
                ->references('id')
                ->on('business')
                ->onDelete('NO ACTION')
                ->onUpdate("NO ACTION");

            $table->timestamps();
        });

        Schema::dropIfExists('delivery_order');


        Schema::dropIfExists('delivery');
        Schema::create('delivery', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('business_id')->unsigned();

            $table->foreign('business_id')
                ->references('id')
                ->on('business')
                ->onDelete('NO ACTION')
                ->onUpdate("NO ACTION");

            $table->timestamps();

            $table->date('delivery_date')->nullable();

            $table->bigInteger('driver_id')->unsigned();

            $table->foreign('driver_id')
                ->references('id')
                ->on('driver')
                ->onDelete('NO ACTION')
                ->onUpdate("NO ACTION");

        });

        Schema::dropIfExists('delivery_order');
        Schema::create('delivery_order', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_id')->unsigned();

            $table->foreign('order_id')
                ->references('id')
                ->on('order')
                ->onDelete('NO ACTION')
                ->onUpdate("NO ACTION");

            $table->timestamps();

            $table->bigInteger('delivery_id')->unsigned();

            $table->foreign('delivery_id')
                ->references('id')
                ->on('delivery')
                ->onDelete('NO ACTION')
                ->onUpdate("NO ACTION");

            $table->integer('delivery_sequence')->unsigned();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver');
        Schema::dropIfExists('itinerary_orders');
        Schema::dropIfExists('itinerary');
    }
};
