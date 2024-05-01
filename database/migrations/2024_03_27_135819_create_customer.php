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
        Schema::dropIfExists('client');
        Schema::create('client', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

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
                ->onUpdate('NO ACTION');

            $table->string('name');
            $table->string('surname');
            $table->string('telephone')->nullable();
            $table->string('phone1')->nullable();
            $table->string('phone2')->nullable();

            $table->string('state')->nullable();
            $table->string('region')->nullable();

            $table->longText('description')->nullable();
            $table->longText('map_link')->nullable();
            $table->bigInteger('changes_count')->unsigned()->default(1);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client');
    }
};
