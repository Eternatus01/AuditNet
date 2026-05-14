<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_comparison_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_comparison_id')->constrained()->onDelete('cascade');
            $table->string('url', 2048);
            $table->unsignedTinyInteger('sort_order')->default(0);
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
            $table->json('security_audit')->nullable();
            $table->json('recommendations')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['audit_comparison_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_comparison_sites');
    }
};
