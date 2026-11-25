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
        Schema::create('barangs_foto', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('id_barang')
                ->nullable()
                ->constrained('barangs')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->text('url_foto_barang')->nullable();

            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs_foto');
    }
};
