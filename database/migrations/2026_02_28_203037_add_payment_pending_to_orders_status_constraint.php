<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'payment_pending' to the orders.status check constraint.
     * The previous migration only listed 'created', 'paid', 'cancelled',
     * which prevents the PAYMENT_PENDING transition introduced by the
     * Stripe payment flow.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return; // SQLite enforces no ALTER TABLE CHECK constraints
        }

        // Drop old constraint (syntax differs by driver)
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_valid_chk');
        } else {
            // mysql / mariadb
            DB::statement('ALTER TABLE orders DROP CHECK orders_status_valid_chk');
        }

        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_valid_chk
            CHECK (status IN ('created', 'payment_pending', 'paid', 'cancelled'))");
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_valid_chk');
        } else {
            DB::statement('ALTER TABLE orders DROP CHECK orders_status_valid_chk');
        }

        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_valid_chk
            CHECK (status IN ('created', 'paid', 'cancelled'))");
    }
};
