<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->constrained()->cascadeOnDelete();
            $table->enum('category', ['product', 'delivery', 'service', 'app', 'other']);
            $table->text('content');
            $table->json('photo_paths')->nullable()->comment('Maks 3 foto');
            $table->enum('status', ['submitted', 'read', 'replied', 'archived'])->default('submitted');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });

        Schema::create('feedback_replies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('feedback_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('admin_id')->constrained('users')->restrictOnDelete();
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_replies');
        Schema::dropIfExists('feedbacks');
    }
};
