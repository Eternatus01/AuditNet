<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitored_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('url', 2048);
            $table->unsignedTinyInteger('schedule_day')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->foreignId('last_audit_id')->nullable()->constrained('audits')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'schedule_day']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitored_sites');
    }
};
