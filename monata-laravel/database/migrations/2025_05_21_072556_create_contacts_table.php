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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('title');
            $table->text('message');
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('contacts');
    }
};
