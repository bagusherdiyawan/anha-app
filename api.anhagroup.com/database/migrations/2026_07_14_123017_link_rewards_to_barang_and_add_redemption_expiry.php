<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->foreignId('barang_id')->nullable()->after('type')->constrained('barangs')->nullOnDelete();
        });

        DB::statement('ALTER TABLE rewards MODIFY name VARCHAR(255) NULL');
        DB::statement('ALTER TABLE rewards MODIFY description TEXT NULL');
        DB::statement('ALTER TABLE rewards MODIFY image VARCHAR(255) NULL');

        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('claimed_at');
        });

        DB::statement("ALTER TABLE reward_redemptions MODIFY status ENUM('pending','claimed','cancelled','expired') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE reward_redemptions MODIFY status ENUM('pending','claimed','cancelled') DEFAULT 'pending'");

        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });

        Schema::table('rewards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('barang_id');
        });

        DB::statement('ALTER TABLE rewards MODIFY name VARCHAR(255) NOT NULL');
    }
};
