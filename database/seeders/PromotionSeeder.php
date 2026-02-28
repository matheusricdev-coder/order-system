<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PromotionSeeder extends Seeder
{
    /**
     * Promotions applied to selected products from ProductSeeder.
     * Products chosen: electronics, games, and fashion to show variety.
     */
    private const PROMOTIONS = [
        // Smartphone Samsung Galaxy S24 – 20% OFF
        [
            'product_id'          => 'aa000001-0000-0000-0000-000000000001',
            'discount_percentage' => 20,
            'days'                => 7,
        ],
        // iPhone 15 Pro – 10% OFF
        [
            'product_id'          => 'aa000001-0000-0000-0000-000000000002',
            'discount_percentage' => 10,
            'days'                => 5,
        ],
        // Fone de Ouvido Sony WH-1000XM5 – 30% OFF
        [
            'product_id'          => 'aa000001-0000-0000-0000-000000000005',
            'discount_percentage' => 30,
            'days'                => 3,
        ],
        // Notebook Dell Inspiron 15 – 15% OFF
        [
            'product_id'          => 'aa000002-0000-0000-0000-000000000001',
            'discount_percentage' => 15,
            'days'                => 10,
        ],
        // MacBook Air M2 – 8% OFF
        [
            'product_id'          => 'aa000002-0000-0000-0000-000000000002',
            'discount_percentage' => 8,
            'days'                => 4,
        ],
        // PlayStation 5 Console – 12% OFF
        [
            'product_id'          => 'aa000003-0000-0000-0000-000000000003',
            'discount_percentage' => 12,
            'days'                => 6,
        ],
        // Nintendo Switch OLED – 25% OFF
        [
            'product_id'          => 'aa000003-0000-0000-0000-000000000004',
            'discount_percentage' => 25,
            'days'                => 8,
        ],
        // Jaqueta Corta-Vento Impermeável – 40% OFF
        [
            'product_id'          => 'aa000004-0000-0000-0000-000000000004',
            'discount_percentage' => 40,
            'days'                => 14,
        ],
    ];

    public function run(): void
    {
        $now = now();

        foreach (self::PROMOTIONS as $promo) {
            DB::table('product_promotions')->upsert(
                [
                    'id'                  => Str::uuid()->toString(),
                    'product_id'          => $promo['product_id'],
                    'discount_percentage' => $promo['discount_percentage'],
                    'is_active'           => true,
                    'starts_at'           => $now,
                    'ends_at'             => $now->copy()->addDays($promo['days']),
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ],
                ['product_id'],   // unique key for upsert
                ['discount_percentage', 'starts_at', 'ends_at', 'is_active', 'updated_at']
            );
        }
    }
}
