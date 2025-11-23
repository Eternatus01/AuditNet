<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('url', 2048);
            $table->tinyInteger('performance')->nullable();
            $table->tinyInteger('accessibility')->nullable();
            $table->tinyInteger('best_practices')->nullable();
            $table->tinyInteger('seo')->nullable();
            $table->decimal('lcp', 8, 2)->nullable();
            $table->decimal('fid', 8, 2)->nullable();
            $table->decimal('cls', 8, 4)->nullable();
            $table->decimal('fcp', 8, 2)->nullable();
            $table->decimal('tbt', 8, 2)->nullable();
            $table->decimal('speed_index', 8, 2)->nullable();
            $table->timestamp('audited_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('created_at');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
