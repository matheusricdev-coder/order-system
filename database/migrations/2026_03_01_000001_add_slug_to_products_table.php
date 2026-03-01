<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        // Populate slug for existing products: slug = kebab(name) + '-' + substr(id, 0, 6)
        $products = DB::table('products')->get(['id', 'name']);
        foreach ($products as $product) {
            $base = Str::slug($product->name);
            $suffix = substr(str_replace('-', '', $product->id), 0, 6);
            DB::table('products')
                ->where('id', $product->id)
                ->update(['slug' => "{$base}-{$suffix}"]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
