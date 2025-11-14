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
        Schema::create('home_page', function (Blueprint $table) {
            $table->id();
            $table->integer('emirate_id')->nullable();
            $table->json('region_ids')->nullable();
            $table->string('title_en')->nullable();
            $table->string('title_ar')->nullable();
            $table->string('title_image_ar_url')->nullable();
            $table->string('title_image_en_url')->nullable();
            $table->string('background_image_url')->nullable();
            $table->string('type')->index()->comment('banners, categories, shops, products, offers, limited_time_offers');
            $table->enum('banner_size', ['small', 'medium', 'large'])->nullable()->comment('Banner size for banner type sections');
            $table->smallInteger('sorting')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_page');
    }
};
