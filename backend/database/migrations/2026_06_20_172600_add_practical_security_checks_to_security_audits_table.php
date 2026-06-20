<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('security_audits', function (Blueprint $table) {
            $table->json('https')->nullable()->after('scripts_info');
            $table->json('header_analysis')->nullable()->after('https');
            $table->json('cookie_flags')->nullable()->after('header_analysis');
            $table->json('mixed_content')->nullable()->after('cookie_flags');
            $table->json('script_integrity')->nullable()->after('mixed_content');
            $table->json('server_exposure')->nullable()->after('script_integrity');
            $table->json('recommendations')->nullable()->after('server_exposure');
            $table->boolean('security_txt')->default(false)->after('sitemap_xml');
        });
    }

    public function down(): void
    {
        Schema::table('security_audits', function (Blueprint $table) {
            $table->dropColumn([
                'https',
                'header_analysis',
                'cookie_flags',
                'mixed_content',
                'script_integrity',
                'server_exposure',
                'recommendations',
                'security_txt',
            ]);
        });
    }
};
