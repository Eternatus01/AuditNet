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
        public readonly ?string $robotsTxt,
        public readonly bool $sitemapXml,
        public readonly array $scriptsInfo,
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
        ];
    }
}

