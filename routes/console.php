<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
api.ts:41 
 POST http://localhost:8081/api/v1/orders/a9282681-393d-419d-9011-b39d78d3d226/pay 500 (Internal Server Error)
request	@	api.ts:41
post	@	api.ts:74
pay	@	api.ts:171
mutationFn	@	useOrders.ts:33
await in execute		
(anonymous)	@	Checkout.tsx:197
await in (anonymous)		
onClick	@	Checkout.tsx:208
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-cancel orders stuck in "created" status for more than 30 minutes
// and release the reserved stock back to the pool.
Schedule::command('orders:cancel-stale --ttl=30')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/cancel-stale-orders.log'));
