<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('user_id');
            $table->text('error_message')->nullable()->after('audited_at');
            
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'error_message']);
        });
    }
};

