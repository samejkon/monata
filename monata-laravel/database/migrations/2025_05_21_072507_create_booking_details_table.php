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
        Schema::create('booking_details', function (Blueprint $table) {
            $table->id();
            $table->integer('room_id');
            $table->integer('booking_id');
            $table->decimal('price_per_day', 10, 2);
            $table->date('checkin_date');
            $table->date('checkout_date');
             $table->integer('creator_id')->nullable();
            $table->integer('last_modifierId')->nullable();
            $table->integer('deleter_id')->nullable();
            $table->softDeletes('deletion_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_details');
    }
};
