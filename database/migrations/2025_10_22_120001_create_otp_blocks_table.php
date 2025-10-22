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
        Schema::create('otp_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('mobile');
            $table->timestamp('blocked_until');
            $table->timestamps();
            
            $table->index(['mobile', 'blocked_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_blocks');
    }
};
