<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SecurityAuditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 2;

    public function __construct(
        public string $url,
        public string $cacheKey,
    ) {}

    public function handle(): void
    {
        try {
            if (!preg_match('/^https?:\/\//', $this->url)) {
                $this->url = 'https://' . $this->url;
            }

            $host = parse_url($this->url, PHP_URL_HOST) ?? $this->url;
            
            $result = [
                'checked_url' => $this->url,
                'host' => $host,
                'headers' => [],
                'sensitive_files' => [],
                'directory_listing' => [],
                'robots_txt' => null,
                'sitemap_xml' => null,
                'scripts_info' => [],
                'cors_headers' => [],
            ];

            $result['headers'] = $this->checkHeaders();
            $result['scripts_info'] = $this->extractScripts();
            
            [$result['sensitive_files'], $result['directory_listing'], $result['robots_txt'], $result['sitemap_xml']] = 
                $this->checkFilesParallel();

            Cache::put($this->cacheKey, $result, now()->addMinutes(10));

            Log::info('Security audit completed', ['url' => $this->url]);

        } catch (\Exception $e) {
            Log::error('Security audit job failed', [
                'url' => $this->url,
                'error' => $e->getMessage()
            ]);
            
            Cache::put($this->cacheKey, ['error' => $e->getMessage()], now()->addMinutes(2));
            
            throw $e;
        }
    }

    protected function checkHeaders(): array
    {
        try {
            $resp = Http::timeout(10)->get($this->url);
            $headers = collect($resp->headers())->mapWithKeys(function ($v, $k) {
                return [strtolower($k) => implode('; ', $v)];
            })->toArray();

            $headersToCheck = [
                'strict-transport-security', 'content-security-policy', 'x-frame-options',
                'referrer-policy', 'permissions-policy', 'x-content-type-options',
                'x-xss-protection', 'cache-control', 'pragma', 'expires', 'access-control-allow-origin'
            ];

            $result = [];
            foreach ($headersToCheck as $header) {
                $result[$header] = isset($headers[$header]) ? $headers[$header] : false;
            }

            return $result;
        } catch (\Exception $e) {
            Log::warning('Headers check failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    protected function extractScripts(): array
    {
        try {
            $resp = Http::timeout(10)->get($this->url);
            if (preg_match_all('#<script[^>]+src="([^"]+)"#i', $resp->body(), $matches)) {
                return $matches[1];
            }
        } catch (\Exception $e) {
            Log::warning('Scripts extraction failed', ['error' => $e->getMessage()]);
        }
        return [];
    }

    protected function checkFilesParallel(): array
    {
        $sensitivePaths = ['/env', '/.env', '/.git/HEAD', '/config.php', '/phpinfo.php', '/.htaccess', '/.DS_Store'];
        $dirs = ['/uploads/', '/files/', '/backup/', '/images/', '/admin/'];
        
        $responses = Http::pool(fn ($pool) => [
            ...array_map(fn($path) => $pool->timeout(5)->get($this->url . $path), $sensitivePaths),
            ...array_map(fn($dir) => $pool->timeout(5)->get($this->url . $dir), $dirs),
            $pool->timeout(5)->get($this->url . '/robots.txt'),
            $pool->timeout(5)->get($this->url . '/sitemap.xml'),
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

    public function failed(\Throwable $exception): void
    {
        Cache::put($this->cacheKey, [
            'error' => 'Не удалось выполнить аудит безопасности: ' . $exception->getMessage()
        ], now()->addMinutes(2));

        Log::error('Security audit job failed permanently', [
            'url' => $this->url,
            'error' => $exception->getMessage()
        ]);
    }
}

