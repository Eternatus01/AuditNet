<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained()->onDelete('cascade');
            $table->string('category');
            $table->string('audit_id_key');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('score', 3, 2)->nullable();
            $table->string('score_display_mode')->nullable();
            $table->string('display_value')->nullable();
            $table->json('details')->nullable();
            $table->integer('numeric_value')->nullable();
            $table->string('numeric_unit')->nullable();
            $table->timestamps();

            $table->index(['audit_id', 'category']);
            $table->index(['audit_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_recommendations');
    }
};
