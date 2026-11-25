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
        Schema::create('alamat_pengiriman', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('label')->comment('Rumah, Kantor, dll');
            $table->string('recipient_name');
            $table->string('phone', 20);
            $table->text('address');
            $table->string('district')->nullable()->comment('Kecamatan');
            $table->string('city');
            $table->string('province');
            $table->string('postal_code', 10);
            $table->text('notes')->nullable()->comment('Catatan kurir');

            $table->boolean('default')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alamat_pengiriman');
    }
};
