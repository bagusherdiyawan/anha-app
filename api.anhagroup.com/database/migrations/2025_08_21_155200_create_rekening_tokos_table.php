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
        Schema::create('rekening_tokos', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nomor')->default('-');
            $table->string('foto')->nullable();
            $table->boolean('status_active')->default(true);
            $table->enum('type', [
                'cod',
                'transfer',
                'wallet',
            ]);
            $table->text('keterangan')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekening_tokos');
    }
};
