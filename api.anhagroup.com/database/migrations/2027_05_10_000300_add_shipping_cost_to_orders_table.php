<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'shipping_cost')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('shipping_cost', 15, 2)->default(0)->after('discount_amount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('orders', 'shipping_cost')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('shipping_cost');
            });
        }
    }
};

