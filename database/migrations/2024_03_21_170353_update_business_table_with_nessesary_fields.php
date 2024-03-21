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
        Schema::table('business',function (Blueprint $table){
            $table->timestamps();
            $table->boolean('isActive')->default(false);
            $table->date('expiration_date')->nullable();
            $table->string('vat')->nullable();
            $table->string('doy')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business',function (Blueprint $table){
            $table->dropColumn('isActive');
            $table->dropColumn('expiration_date');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('vat');
            $table->dropColumn('doy');
        });
    }
};
