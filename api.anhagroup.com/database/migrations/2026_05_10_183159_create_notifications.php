<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->constrained()->cascadeOnDelete();
            $table->enum('category', ['order', 'shipping', 'points', 'voucher', 'feedback', 'promo', 'system']);
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable()->comment('Payload tambahan untuk deep link');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index('category');
        });

        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->enum('platform', ['android', 'ios']);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('push_notifications');
    }
};
