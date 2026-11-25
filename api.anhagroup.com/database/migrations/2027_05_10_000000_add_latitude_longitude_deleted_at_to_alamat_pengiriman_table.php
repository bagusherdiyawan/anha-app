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
        Schema::table('alamat_pengiriman', function (Blueprint $table) {
            if (!Schema::hasColumn('alamat_pengiriman', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('alamat_pengiriman', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('alamat_pengiriman', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alamat_pengiriman', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'deleted_at']);
        });
    }
};
