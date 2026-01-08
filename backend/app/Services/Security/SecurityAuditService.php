<?php

namespace App\Services\Security;

use App\DTOs\SecurityAuditResultDTO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SecurityAuditService
{
    public function auditWebsite(string $url): SecurityAuditResultDTO
    {
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }

        $host = parse_url($url, PHP_URL_HOST) ?? $url;

        $headers = $this->checkSecurityHeaders($url);
        $scriptsInfo = $this->analyzeScripts($url);
        [$sensitiveFiles, $directoryListing, $robotsTxt, $sitemapXml] = $this->checkFilesParallel($url);

        return new SecurityAuditResultDTO(
            checkedUrl: $url,
            host: $host,
            headers: $headers,
            sensitiveFiles: $sensitiveFiles,
            directoryListing: $directoryListing,
            robotsTxt: $robotsTxt,
            sitemapXml: $sitemapXml,
            scriptsInfo: $scriptsInfo,
        );
    }

    private function checkSecurityHeaders(string $url): array
    {
        try {
            $resp = Http::timeout(10)->get($url);
            $headers = collect($resp->headers())->mapWithKeys(function ($v, $k) {
                return [strtolower($k) => implode('; ', $v)];
            })->toArray();

            $headersToCheck = [
                'strict-transport-security',
                'content-security-policy',
                'x-frame-options',
                'referrer-policy',
                'permissions-policy',
                'x-content-type-options',
                'x-xss-protection',
                'cache-control',
                'pragma',
                'expires',
                'access-control-allow-origin'
            ];

            $result = [];
            foreach ($headersToCheck as $header) {
                $result[$header] = $headers[$header] ?? false;
            }

            return $result;
        } catch (\Exception $e) {
            Log::warning('Headers check failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function analyzeScripts(string $url): array
    {
        try {
            $resp = Http::timeout(10)->get($url);
            if (preg_match_all('#<script[^>]+src="([^"]+)"#i', $resp->body(), $matches)) {
                return $matches[1];
            }
        } catch (\Exception $e) {
            Log::warning('Scripts extraction failed', ['error' => $e->getMessage()]);
        }
        return [];
    }

    private function checkFilesParallel(string $url): array
    {
        $sensitivePaths = ['/env', '/.env', '/.git/HEAD', '/config.php', '/phpinfo.php', '/.htaccess', '/.DS_Store'];
        $dirs = ['/uploads/', '/files/', '/backup/', '/images/', '/admin/'];

        $responses = Http::pool(fn ($pool) => [
            ...array_map(fn($path) => $pool->timeout(5)->get($url . $path), $sensitivePaths),
            ...array_map(fn($dir) => $pool->timeout(5)->get($url . $dir), $dirs),
            $pool->timeout(5)->get($url . '/robots.txt'),
            $pool->timeout(5)->get($url . '/sitemap.xml'),
        ]);

        $sensitiveFiles = [];
        foreach ($sensitivePaths as $idx => $path) {
            $resp = $responses[$idx];
            $sensitiveFiles[$path] = $resp instanceof \Illuminate\Http\Client\Response
                ? in_array($resp->status(), [200, 401, 403])
                : false;
        }

        $directoryListing = [];
        $offset = count($sensitivePaths);
        foreach ($dirs as $idx => $dir) {
            $resp = $responses[$offset + $idx];
            if ($resp instanceof \Illuminate\Http\Client\Response) {
                $isListing = (
                    strpos($resp->body(), 'Index of') !== false ||
                    preg_match('/<title>Directory listing/i', $resp->body())
                );
                $directoryListing[$dir] = $isListing;
            } else {
                $directoryListing[$dir] = false;
            }
        }

        $robotsIdx = $offset + count($dirs);
        $robotsTxt = $responses[$robotsIdx] instanceof \Illuminate\Http\Client\Response && $responses[$robotsIdx]->ok()
            ? $responses[$robotsIdx]->body()
            : null;

        $sitemapIdx = $robotsIdx + 1;
        $sitemapXml = $responses[$sitemapIdx] instanceof \Illuminate\Http\Client\Response && $responses[$sitemapIdx]->ok();

        return [$sensitiveFiles, $directoryListing, $robotsTxt, $sitemapXml];
    }
}

