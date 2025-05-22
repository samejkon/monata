<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('email', 125)->unique();
            $table->string('password', 255);
            $table->string('phone', 20)->nullable();
            $table->integer('creator_id')->nullable();
            $table->integer('last_modifierId')->nullable();
            $table->integer('deleter_id')->nullable();
            $table->softDeletes('deletion_time')->nullable();
            $table->timestamps();
        });

        DB::table('admins')->insert([
            'name' => 'Admin',
            'email' => 'admin@tomosia.com',
            'password' => Hash::make('123456')
        ]);
    }



    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
