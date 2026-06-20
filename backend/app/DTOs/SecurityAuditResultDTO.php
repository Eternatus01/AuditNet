<?php

namespace App\DTOs;

class SecurityAuditResultDTO
{
    public function __construct(
        public readonly string $checkedUrl,
        public readonly string $host,
        public readonly array $headers,
        public readonly array $sensitiveFiles,
        public readonly array $directoryListing,
        public readonly bool $robotsTxt,
        public readonly bool $sitemapXml,
        public readonly array $scriptsInfo,
        public readonly array $https,
        public readonly array $headerAnalysis,
        public readonly array $cookieFlags,
        public readonly array $mixedContent,
        public readonly array $scriptIntegrity,
        public readonly array $serverExposure,
        public readonly bool $securityTxt,
        public readonly array $recommendations,
    ) {}

    public function toArray(): array
    {
        return [
            'checked_url' => $this->checkedUrl,
            'host' => $this->host,
            'headers' => $this->headers,
            'sensitive_files' => $this->sensitiveFiles,
            'directory_listing' => $this->directoryListing,
            'robots_txt' => $this->robotsTxt,
            'sitemap_xml' => $this->sitemapXml,
            'scripts_info' => $this->scriptsInfo,
            'https' => $this->https,
            'header_analysis' => $this->headerAnalysis,
            'cookie_flags' => $this->cookieFlags,
            'mixed_content' => $this->mixedContent,
            'script_integrity' => $this->scriptIntegrity,
            'server_exposure' => $this->serverExposure,
            'security_txt' => $this->securityTxt,
            'recommendations' => $this->recommendations,
        ];
    }
}

