<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('website_url')->nullable();
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('catalog');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('status')->default('draft');
            $table->integer('position')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('slug')->unique();
            $table->string('type')->default('standard');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('default_variant_id')->nullable();
            $table->string('name');
            $table->string('subtitle')->nullable();
            $table->string('sku')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('description')->nullable();
            $table->json('specifications')->nullable();
            $table->json('data')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'brand_id']);
        });

        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->integer('position')->default(0);
            $table->timestamps();
            $table->unique(['category_id', 'product_id']);
        });

        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('input_type')->default('select');
            $table->boolean('is_required')->default(true);
            $table->integer('position')->default(0);
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['product_id', 'code']);
        });

        Schema::create('product_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_id')->constrained('product_options')->cascadeOnDelete();
            $table->string('value');
            $table->string('display_value')->nullable();
            $table->string('hex_value')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->integer('position')->default(0);
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['product_option_id', 'value']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->string('status')->default('draft');
            $table->decimal('price', 12, 2);
            $table->decimal('compare_at_price', 12, 2)->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('inventory_sku')->nullable();
            $table->string('inventory_policy')->default('deny');
            $table->integer('inventory_quantity')->default(0);
            $table->boolean('track_inventory')->default(true);
            $table->decimal('weight', 10, 3)->nullable();
            $table->string('weight_unit', 10)->nullable();
            $table->decimal('length', 10, 3)->nullable();
            $table->decimal('width', 10, 3)->nullable();
            $table->decimal('height', 10, 3)->nullable();
            $table->string('dimension_unit', 10)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('requires_shipping')->default(true);
            $table->boolean('requires_serial')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['product_id', 'status']);
        });

        Schema::create('product_variant_option_value', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('product_option_value_id')->constrained('product_option_values')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_variant_id', 'product_option_value_id'], 'variant_option_unique');
        });

        Schema::create('product_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('type')->default('image');
            $table->string('disk')->nullable();
            $table->string('path');
            $table->string('url')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('position')->default(0);
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->default('text');
            $table->string('unit')->nullable();
            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_required')->default(false);
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->string('value');
            $table->string('display_value')->nullable();
            $table->decimal('numeric_value', 12, 4)->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['attribute_id', 'value']);
        });

        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->nullable()->constrained('attribute_values')->nullOnDelete();
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 12, 4)->nullable();
            $table->json('value_json')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'attribute_id', 'attribute_value_id'], 'product_attribute_unique');
        });

        Schema::create('related_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
            $table->string('relation_type')->default('related');
            $table->integer('position')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'related_product_id', 'relation_type'], 'related_products_unique');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('default_variant_id')->references('id')->on('product_variants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['default_variant_id']);
        });

        Schema::dropIfExists('related_products');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('product_media');
        Schema::dropIfExists('product_variant_option_value');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_option_values');
        Schema::dropIfExists('product_options');
        Schema::dropIfExists('category_product');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('brands');
    }
};
