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
        Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->string('target_page')->comment('home_page, product_details, shop_details, category_details');
            $table->string('size')->comment('small, medium, fullscreen');
            $table->string('image_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('video_url')->nullable();
            $table->string('link')->nullable();
            $table->string('item_type')->nullable()->comment('product, shop, category');
            $table->unsignedBigInteger('item_id')->nullable()->comment('product_id, shop_id, category_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popups');
    }
};
