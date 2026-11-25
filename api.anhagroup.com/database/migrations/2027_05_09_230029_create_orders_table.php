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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table
                ->bigInteger('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('invoice')->unique();

            $table->decimal('total', 15, 2);

            $table->enum('status', [
                'pending',
                'paid',
                'cancelled',
                'completed'
            ])->default('pending');

            $table->integer('alamat_pengiriman');
            $table->integer('metode_pembayaran_id');
            $table->integer('estimated_points')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
