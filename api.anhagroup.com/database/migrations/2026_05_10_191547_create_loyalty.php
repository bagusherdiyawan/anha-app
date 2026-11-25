<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['earn', 'redeem', 'adjustment', 'expired']);
            $table->integer('amount')->comment('Positif = masuk, negatif = keluar');
            $table->integer('balance_after');
            $table->string('reference_type')->nullable()->comment('App\Models\Order, dll');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description');
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['voucher_discount', 'direct_discount', 'physical_item']);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('points_required');
            $table->integer('stock')->default(0)->comment('Stock untuk physical_item; -1 = unlimited');
            $table->decimal('discount_value', 15, 2)->nullable()->comment('Nominal/persen diskon untuk voucher/direct');
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('max_discount', 15, 2)->nullable()->comment('Maks diskon untuk tipe persentase');
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
        });

        Schema::create('reward_redemptions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('reward_id')->constrained()->restrictOnDelete();
            $table->integer('points_spent');
            $table->string('claim_code')->unique()->nullable()->comment('Kode klaim untuk physical_item');
            $table->enum('status', ['pending', 'claimed', 'cancelled'])->default('pending');
            $table->bigInteger('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('claim_code');
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('reward_redemption_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->decimal('discount_value', 15, 2);
            $table->enum('discount_type', ['fixed', 'percentage']);
            $table->decimal('max_discount', 15, 2)->nullable();
            $table->decimal('min_purchase', 15, 2)->default(0);
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->bigInteger('used_in_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'is_used']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('reward_redemptions');
        Schema::dropIfExists('rewards');
        Schema::dropIfExists('point_transactions');
    }
};
