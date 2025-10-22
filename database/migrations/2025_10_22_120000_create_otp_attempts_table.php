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
        Schema::create('otp_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('mobile');
            $table->integer('attempts');
            $table->timestamps();
            
            $table->index(['mobile', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_attempts');
    }
};
