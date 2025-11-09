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
        // Navigation menus (containers)
        Schema::create('navigation_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->unique(); // primary, utility, footer, mobile
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Navigation items (menu entries)
        Schema::create('navigation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('navigation_menus')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('navigation_items')->cascadeOnDelete();
            $table->string('label');
            $table->string('url')->nullable();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_mega')->default(false);
            $table->boolean('is_external')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);
            $table->json('hero')->nullable(); // mega menu hero content
            $table->timestamps();

            $table->index(['menu_id', 'parent_id', 'position']);
        });

        // Mega menu columns
        Schema::create('navigation_mega_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('navigation_item_id')->constrained('navigation_items')->cascadeOnDelete();
            $table->string('heading');
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['navigation_item_id', 'position']);
        });

        // Mega menu items within columns
        Schema::create('navigation_mega_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mega_column_id')->constrained('navigation_mega_columns')->cascadeOnDelete();
            $table->string('label');
            $table->string('url');
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['mega_column_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('navigation_mega_items');
        Schema::dropIfExists('navigation_mega_columns');
        Schema::dropIfExists('navigation_items');
        Schema::dropIfExists('navigation_menus');
    }
};
