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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained('categories')
                ->onDelete('set null');

            $table->string('slug')->unique()->after('name');
            $table->string('image_url')->nullable()->after('description');
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('price');

            $table->index('category_id');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['slug']);
            $table->dropColumn(['category_id', 'slug', 'image_url', 'discount_percentage']);
        });
    }
};
