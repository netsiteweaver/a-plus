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
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('type'); // hero, featured_categories, featured_products, daily_deals, promo_banner, text_block, image_block
            $table->string('title')->nullable();
            $table->json('content'); // all block data stored as JSON
            $table->string('page')->default('home'); // home, about, services, etc.
            $table->string('status')->default('published'); // draft, published, archived
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['page', 'status', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
    }
};
