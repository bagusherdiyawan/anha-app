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
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();

            $table->string('nama_barang');

            $table->string('kode_barang', 100)->nullable();

            $table->string('barcode')->nullable();

            $table->string('satuan')->nullable();

            $table->text('deskripsi');
            $table->integer('stock')->default(0);

            $table->string('kode_kategori', 100)->nullable();

            $table->decimal('rating', 10, 0)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
