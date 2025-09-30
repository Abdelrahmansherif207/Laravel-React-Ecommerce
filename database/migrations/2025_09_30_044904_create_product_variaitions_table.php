<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('variation_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->timestamps();
        });

        Schema::create('variation_type_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variation_type_id')
                ->index()
                ->constrained('variation_types')
                ->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->index()
                ->constrained('products')
                ->cascadeOnDelete();
            $table->json('variation_type_option_ids');
            $table->integer('quantity')->nullable();
            $table->decimal('price', 20, 4)->nullable();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variations');
        Schema::dropIfExists('variation_type_options');
        Schema::dropIfExists('variation_types');
    }
};
