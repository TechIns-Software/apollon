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
        Schema::create('order', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();

            $table->bigInteger('client_id')->unsigned();

            $table->foreign('client_id')
                ->references('id')
                ->on('client')
                ->onDelete('NO ACTION')
                ->onUpdate('NO ACTION');

            $table->bigInteger('business_id')->unsigned();

            $table->foreign('business_id')
                ->references('id')
                ->on('business')
                ->onDelete('NO ACTION')
                ->onUpdate("NO ACTION");

            $table->bigInteger('saas_user_id')->unsigned();

            $table->foreign('saas_user_id')
                ->references('id')
                ->on('saas_user')
                ->onDelete('NO ACTION')
                ->onUpdate("NO ACTION");

            $table->longText('description')->nullable();
            $table->enum('status',['OPEN','FINISHED','CANCELLED'])->default('OPEN');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order');
    }
};
