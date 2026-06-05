<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->foreignId('monitored_site_id')->nullable()->after('user_id')->constrained('monitored_sites')->nullOnDelete();
            $table->string('source')->default('manual')->after('status');

            $table->index(['monitored_site_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropIndex(['monitored_site_id', 'created_at']);
            $table->dropConstrainedForeignId('monitored_site_id');
            $table->dropColumn('source');
        });
    }
};
