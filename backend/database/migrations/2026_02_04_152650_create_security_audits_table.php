<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained()->onDelete('cascade');
            $table->json('headers')->nullable();
            $table->json('sensitive_files')->nullable();
            $table->json('directory_listing')->nullable();
            $table->json('scripts_info')->nullable();
            $table->boolean('robots_txt')->default(false);
            $table->boolean('sitemap_xml')->default(false);
            $table->string('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_audits');
    }
};
