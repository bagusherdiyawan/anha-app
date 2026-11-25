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
        Schema::create('keranjangs', function (Blueprint $table) {
            $table->id();

            $table
                ->bigInteger('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table
                ->foreignId('barang_id')
                ->constrained('barangs')
                ->onDelete('cascade');

            $table->integer('qty')->default(1);
            $table->decimal('harga', 15, 2);
            $table->decimal('subtotal', 15, 2);

            $table->timestamps();

            // supaya 1 barang tidak dobel di keranjang user
            $table->unique(['user_id', 'barang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keranjangs');
    }
};
