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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->constrained()->onDelete('cascade');
            $table
                ->bigInteger('alamat_id')
                ->nullable()
                ->constrained('alamat_pengiriman')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->integer('biaya_antar_id');
            $table->string('barang_id');
            $table->decimal('total', 10, 2);
            $table->string('method_bayar');
            $table->integer('rektoko_id');
            $table->boolean('status_bayar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
