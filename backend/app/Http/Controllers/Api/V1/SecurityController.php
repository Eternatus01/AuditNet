<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class SecurityController extends Controller
{
    public function analyze(Request $request)
    {
        $url = $request->input('url');
        if (!$url) {
            return response()->json(['error' => 'No url provided'], 400);
        }

        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }

        $host = parse_url($url, PHP_URL_HOST) ?? $url;
        $result = [
            'checked_url' => $url,
            'host' => $host,
            'headers' => [],
            'sensitive_files' => [],
            'directory_listing' => [],
            'robots_txt' => null,
            'sitemap_xml' => null,
            'scripts_info' => [],
            'cors_headers' => [],
        ];

        // Проверка security заголовков
        try {
            $resp = Http::timeout(10)->get($url);
            $headers = collect($resp->headers())->mapWithKeys(function ($v, $k) {
                return [strtolower($k) => implode('; ', $v)];
            })->toArray();

            $headersToCheck = [
                'strict-transport-security', 'content-security-policy', 'x-frame-options',
                'referrer-policy', 'permissions-policy', 'x-content-type-options',
                'x-xss-protection', 'cache-control', 'pragma', 'expires', 'access-control-allow-origin'
            ];
            foreach ($headersToCheck as $header) {
                $result['headers'][$header] = isset($headers[$header]) ? $headers[$header] : false;
            }
            // Дополнительно CORS-проявления
            $result['cors_headers'] = [
                'access-control-allow-origin' => $headers['access-control-allow-origin'] ?? null,
                'access-control-allow-credentials' => $headers['access-control-allow-credentials'] ?? null,
            ];

            if (preg_match_all('#<script[^>]+src="([^"]+)"#i', $resp->body(), $matches)) {
                $result['scripts_info'] = $matches[1];
            }
        } catch (\Exception $e) {
            $result['headers_error'] = $e->getMessage();
        }

        // Проверка чувствительных файлов
        $sensitivePaths = ['/env', '/.env', '/.git/HEAD', '/config.php', '/phpinfo.php', '/.htaccess', '/.DS_Store'];
        foreach ($sensitivePaths as $path) {
            try {
                $fileResp = Http::timeout(5)->get($url . $path);
                $status = $fileResp->status();
                $result['sensitive_files'][$path] = in_array($status, [200, 401, 403]);
            } catch (\Exception $e) {
                $result['sensitive_files'][$path] = false;
            }
        }

        // Проверка directory listing
        $dirs = ['/uploads/', '/files/', '/backup/', '/images/', '/admin/'];
        foreach ($dirs as $dir) {
            try {
                $dirResp = Http::timeout(5)->get($url . $dir);
                $isListing = (
                    strpos($dirResp->body(), 'Index of') !== false ||
                    preg_match('/<title>Directory listing/i', $dirResp->body())
                );
                $result['directory_listing'][$dir] = $isListing ? true : false;
            } catch (\Exception $e) {
                $result['directory_listing'][$dir] = false;
            }
        }

        // ROBOTS.TXT
        try {
            $robotsResp = Http::timeout(5)->get($url . '/robots.txt');
            $result['robots_txt'] = $robotsResp->ok() ? $robotsResp->body() : null;
        } catch (\Exception $e) {
            $result['robots_txt'] = null;
        }

        // SITEMAP.XML
        try {
            $sitemapResp = Http::timeout(5)->get($url . '/sitemap.xml');
            $result['sitemap_xml'] = $sitemapResp->ok() ? true : false;
        } catch (\Exception $e) {
            $result['sitemap_xml'] = false;
        }

        return response()->json($result);
    }
}