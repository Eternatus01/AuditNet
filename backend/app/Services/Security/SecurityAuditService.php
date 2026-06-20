<?php

namespace App\Services\Security;

use App\DTOs\SecurityAuditResultDTO;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SecurityAuditService
{
    public function auditWebsite(string $url): SecurityAuditResultDTO
    {
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }

        $host = parse_url($url, PHP_URL_HOST) ?? $url;

        $pageResponse = $this->fetchPage($url);
        $headers = $this->extractHeaders($pageResponse);
        $body = $pageResponse?->body() ?? '';

        $securityHeaders = $this->checkSecurityHeaders($headers);
        $headerAnalysis = $this->analyzeHeaderQuality($headers);
        $scriptsInfo = $this->analyzeScripts($url, $host, $body);
        $https = $this->checkHttps($url, $host);
        $cookieFlags = $this->analyzeCookies($pageResponse);
        $mixedContent = $this->checkMixedContent($url, $body);
        $scriptIntegrity = $this->checkScriptIntegrity($scriptsInfo);
        $serverExposure = $this->checkServerExposure($headers);
        [$sensitiveFiles, $directoryListing, $robotsTxt, $sitemapXml, $securityTxt] = $this->checkFilesParallel($url);
        $recommendations = $this->buildRecommendations(
            $https,
            $headerAnalysis,
            $cookieFlags,
            $mixedContent,
            $scriptIntegrity,
            $serverExposure,
            $sensitiveFiles,
            $directoryListing
        );

        return new SecurityAuditResultDTO(
            checkedUrl: $url,
            host: $host,
            headers: $securityHeaders,
            sensitiveFiles: $sensitiveFiles,
            directoryListing: $directoryListing,
            robotsTxt: $robotsTxt,
            sitemapXml: $sitemapXml,
            scriptsInfo: $scriptsInfo,
            https: $https,
            headerAnalysis: $headerAnalysis,
            cookieFlags: $cookieFlags,
            mixedContent: $mixedContent,
            scriptIntegrity: $scriptIntegrity,
            serverExposure: $serverExposure,
            securityTxt: $securityTxt,
            recommendations: $recommendations,
        );
    }

    private function fetchPage(string $url): ?Response
    {
        try {
            return Http::timeout(30)->get($url);
        } catch (\Exception) {
            return null;
        }
    }

    private function extractHeaders(?Response $response): array
    {
        if (!$response) {
            return [];
        }

        return collect($response->headers())->mapWithKeys(function ($v, $k) {
            return [strtolower($k) => implode('; ', $v)];
        })->toArray();
    }

    private function checkSecurityHeaders(array $headers): array
    {
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
    }

    private function analyzeHeaderQuality(array $headers): array
    {
        return [
            'strict-transport-security' => $this->analyzeHsts($headers['strict-transport-security'] ?? null),
            'content-security-policy' => $this->analyzeCsp($headers['content-security-policy'] ?? null),
            'x-frame-options' => $this->analyzeSimpleHeader(
                $headers['x-frame-options'] ?? null,
                'Защищает от clickjacking через запрет встраивания сайта в iframe.',
                'Добавьте X-Frame-Options: SAMEORIGIN или используйте CSP frame-ancestors.'
            ),
            'x-content-type-options' => $this->analyzeNosniff($headers['x-content-type-options'] ?? null),
            'referrer-policy' => $this->analyzeReferrerPolicy($headers['referrer-policy'] ?? null),
            'permissions-policy' => $this->analyzeSimpleHeader(
                $headers['permissions-policy'] ?? null,
                'Ограничивает доступ страницы к API браузера: камера, геолокация, микрофон.',
                'Добавьте Permissions-Policy и запретите функции, которые сайту не нужны.'
            ),
        ];
    }

    private function analyzeHsts(?string $value): array
    {
        if (!$value) {
            return $this->check('bad', null, 'HSTS отсутствует.', 'Добавьте Strict-Transport-Security с max-age минимум 31536000.');
        }

        preg_match('/max-age=(\d+)/i', $value, $match);
        $maxAge = isset($match[1]) ? (int) $match[1] : 0;

        if ($maxAge < 31536000) {
            return $this->check('warn', $value, 'HSTS есть, но max-age меньше 1 года.', 'Увеличьте max-age до 31536000 и проверьте includeSubDomains.');
        }

        return $this->check('ok', $value, 'HSTS настроен хорошо.', 'Дополнительных действий не требуется.');
    }

    private function analyzeCsp(?string $value): array
    {
        if (!$value) {
            return $this->check('bad', null, 'CSP отсутствует.', 'Добавьте Content-Security-Policy, чтобы снизить риск XSS.');
        }

        $issues = [];
        if (str_contains(strtolower($value), "'unsafe-inline'")) {
            $issues[] = 'используется unsafe-inline';
        }
        if (str_contains(strtolower($value), "'unsafe-eval'")) {
            $issues[] = 'используется unsafe-eval';
        }
        if (preg_match('/script-src[^;]*\*/i', $value)) {
            $issues[] = 'script-src разрешает любые источники';
        }

        if ($issues) {
            return $this->check('warn', $value, 'CSP есть, но ослаблен: ' . implode(', ', $issues) . '.', 'Уберите unsafe-inline/unsafe-eval и ограничьте script-src конкретными доменами.');
        }

        return $this->check('ok', $value, 'CSP настроен без явных слабых правил.', 'Проверьте, что политика не ломает нужные скрипты.');
    }

    private function analyzeNosniff(?string $value): array
    {
        if (strtolower((string) $value) !== 'nosniff') {
            return $this->check('bad', $value, 'X-Content-Type-Options не настроен как nosniff.', 'Добавьте X-Content-Type-Options: nosniff.');
        }

        return $this->check('ok', $value, 'Браузер не будет угадывать MIME-тип файлов.', 'Дополнительных действий не требуется.');
    }

    private function analyzeReferrerPolicy(?string $value): array
    {
        if (!$value) {
            return $this->check('warn', null, 'Referrer-Policy отсутствует.', 'Добавьте Referrer-Policy: strict-origin-when-cross-origin.');
        }

        if (in_array(strtolower($value), ['unsafe-url', 'no-referrer-when-downgrade'], true)) {
            return $this->check('warn', $value, 'Referrer-Policy раскрывает слишком много данных.', 'Используйте strict-origin-when-cross-origin или no-referrer.');
        }

        return $this->check('ok', $value, 'Referrer-Policy настроен.', 'Дополнительных действий не требуется.');
    }

    private function analyzeSimpleHeader(?string $value, string $okMessage, string $fix): array
    {
        if (!$value) {
            return $this->check('warn', null, 'Заголовок отсутствует.', $fix);
        }

        return $this->check('ok', $value, $okMessage, 'Дополнительных действий не требуется.');
    }

    private function check(string $status, ?string $value, string $message, string $recommendation): array
    {
        return compact('status', 'value', 'message', 'recommendation');
    }

    private function analyzeScripts(string $url, string $host, string $body): array
    {
        if (!$body) {
            return [];
        }

        preg_match_all('#<script\b([^>]*)\bsrc=["\']([^"\']+)["\']([^>]*)>#i', $body, $matches, PREG_SET_ORDER);

        return array_map(function ($match) use ($url, $host) {
            $attrs = $match[1] . ' ' . $match[3];
            $src = $this->absoluteUrl($match[2], $url);
            $scriptHost = parse_url($src, PHP_URL_HOST);

            return [
                'src' => $src,
                'external' => $scriptHost && $scriptHost !== $host,
                'integrity' => preg_match('/\bintegrity=["\'][^"\']+["\']/i', $attrs) === 1,
                'async' => preg_match('/\basync\b/i', $attrs) === 1,
                'defer' => preg_match('/\bdefer\b/i', $attrs) === 1,
            ];
        }, $matches);
    }

    private function checkHttps(string $url, string $host): array
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $httpUrl = 'http://' . $host;

        try {
            $resp = Http::timeout(15)->withoutRedirecting()->get($httpUrl);
            $location = $resp->header('Location');
            $redirectsToHttps = in_array($resp->status(), [301, 302, 303, 307, 308], true)
                && is_string($location)
                && str_starts_with(strtolower($location), 'https://');

            return [
                'uses_https' => $scheme === 'https',
                'http_to_https_redirect' => $redirectsToHttps,
                'http_status' => $resp->status(),
                'redirect_location' => $location,
            ];
        } catch (\Exception) {
            return [
                'uses_https' => $scheme === 'https',
                'http_to_https_redirect' => null,
                'http_status' => null,
                'redirect_location' => null,
            ];
        }
    }

    private function analyzeCookies(?Response $response): array
    {
        $responseHeaders = $response?->headers() ?? [];
        $cookies = $responseHeaders['Set-Cookie'] ?? $responseHeaders['set-cookie'] ?? [];
        $cookies = is_array($cookies) ? $cookies : [$cookies];

        $items = [];
        foreach ($cookies as $cookie) {
            $name = trim(explode('=', $cookie, 2)[0] ?? '');
            if (!$name) {
                continue;
            }

            $lower = strtolower($cookie);
            $issues = [];
            if (!str_contains($lower, '; secure')) {
                $issues[] = 'нет Secure';
            }
            if (!str_contains($lower, '; httponly')) {
                $issues[] = 'нет HttpOnly';
            }
            if (!str_contains($lower, 'samesite=')) {
                $issues[] = 'нет SameSite';
            }

            $items[] = [
                'name' => $name,
                'secure' => str_contains($lower, '; secure'),
                'httponly' => str_contains($lower, '; httponly'),
                'samesite' => preg_match('/samesite=([^;]+)/i', $cookie, $match) ? $match[1] : null,
                'issues' => $issues,
            ];
        }

        return [
            'cookies' => $items,
            'total' => count($items),
            'weak' => count(array_filter($items, fn ($item) => count($item['issues']) > 0)),
        ];
    }

    private function checkMixedContent(string $url, string $body): array
    {
        $isHttps = parse_url($url, PHP_URL_SCHEME) === 'https';
        preg_match_all('#(?:src|href|action)=["\'](http://[^"\']+)["\']#i', $body, $matches);
        $examples = array_values(array_unique($matches[1] ?? []));

        return [
            'checked' => $isHttps,
            'count' => $isHttps ? count($examples) : 0,
            'examples' => array_slice($examples, 0, 8),
        ];
    }

    private function checkScriptIntegrity(array $scriptsInfo): array
    {
        $external = array_values(array_filter($scriptsInfo, fn ($script) => $script['external'] ?? false));
        $withoutIntegrity = array_values(array_filter($external, fn ($script) => !($script['integrity'] ?? false)));

        return [
            'external_count' => count($external),
            'without_integrity_count' => count($withoutIntegrity),
            'examples' => array_slice(array_map(fn ($script) => $script['src'], $withoutIntegrity), 0, 8),
        ];
    }

    private function checkServerExposure(array $headers): array
    {
        $server = $headers['server'] ?? null;
        $poweredBy = $headers['x-powered-by'] ?? null;

        return [
            'server' => $server,
            'x_powered_by' => $poweredBy,
            'issues' => array_values(array_filter([
                $server ? 'Заголовок Server раскрывает используемый веб-сервер.' : null,
                $poweredBy ? 'X-Powered-By раскрывает технологию backend.' : null,
            ])),
        ];
    }

    private function absoluteUrl(string $src, string $baseUrl): string
    {
        if (preg_match('/^https?:\/\//i', $src)) {
            return $src;
        }
        if (str_starts_with($src, '//')) {
            return 'https:' . $src;
        }

        $parts = parse_url($baseUrl);
        $origin = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '');

        return str_starts_with($src, '/') ? $origin . $src : rtrim($origin, '/') . '/' . ltrim($src, '/');
    }

    private function buildRecommendations(
        array $https,
        array $headerAnalysis,
        array $cookieFlags,
        array $mixedContent,
        array $scriptIntegrity,
        array $serverExposure,
        array $sensitiveFiles,
        array $directoryListing
    ): array {
        $items = [];

        if (!($https['uses_https'] ?? false)) {
            $items[] = $this->recommendation('critical', 'Сайт открыт без HTTPS', 'Подключите TLS-сертификат и отдавайте сайт только по HTTPS.');
        }
        if (($https['http_to_https_redirect'] ?? null) === false) {
            $items[] = $this->recommendation('high', 'HTTP не перенаправляет на HTTPS', 'Настройте 301/308 редирект с http:// на https://.');
        }
        foreach ($headerAnalysis as $name => $check) {
            if (($check['status'] ?? 'ok') !== 'ok') {
                $items[] = $this->recommendation($check['status'] === 'bad' ? 'high' : 'medium', strtoupper($name), $check['recommendation']);
            }
        }
        if (($cookieFlags['weak'] ?? 0) > 0) {
            $items[] = $this->recommendation('high', 'Слабые cookie-флаги', 'Для сессионных cookies включите Secure, HttpOnly и SameSite=Lax/Strict.');
        }
        if (($mixedContent['count'] ?? 0) > 0) {
            $items[] = $this->recommendation('high', 'Mixed content', 'Замените все http:// ресурсы на https://.');
        }
        if (($scriptIntegrity['without_integrity_count'] ?? 0) > 0) {
            $items[] = $this->recommendation('medium', 'Внешние скрипты без SRI', 'Для CDN-скриптов добавьте integrity и crossorigin.');
        }
        if (($serverExposure['issues'] ?? [])) {
            $items[] = $this->recommendation('low', 'Раскрытие технологий сервера', 'Скрывайте или минимизируйте Server и X-Powered-By в конфигурации сервера.');
        }
        foreach ($sensitiveFiles as $path => $found) {
            if ($found) {
                $items[] = $this->recommendation('critical', "Доступен чувствительный файл {$path}", 'Закройте доступ к файлу на уровне web-сервера и проверьте, не утекли ли секреты.');
            }
        }
        foreach ($directoryListing as $path => $enabled) {
            if ($enabled) {
                $items[] = $this->recommendation('high', "Включён listing директории {$path}", 'Отключите autoindex/directory listing для публичных директорий.');
            }
        }

        return $items;
    }

    private function recommendation(string $severity, string $title, string $fix): array
    {
        return compact('severity', 'title', 'fix');
    }

    private function checkFilesParallel(string $url): array
    {
        $sensitivePaths = [
            '/env',
            '/.env',
            '/.env.local',
            '/.env.backup',
            '/.git/HEAD',
            '/config.php',
            '/wp-config.php',
            '/phpinfo.php',
            '/server-status',
            '/composer.json',
            '/package-lock.json',
            '/backup.zip',
            '/backup.tar.gz',
            '/dump.sql',
            '/database.sql',
            '/.htaccess',
            '/.DS_Store',
        ];
        $dirs = ['/uploads/', '/files/', '/backup/', '/images/', '/admin/'];

        $responses = Http::pool(fn ($pool) => [
            ...array_map(fn($path) => $pool->timeout(15)->get($url . $path), $sensitivePaths),
            ...array_map(fn($dir) => $pool->timeout(15)->get($url . $dir), $dirs),
            $pool->timeout(15)->get($url . '/robots.txt'),
            $pool->timeout(15)->get($url . '/sitemap.xml'),
            $pool->timeout(15)->get($url . '/.well-known/security.txt'),
        ]);

        $sensitiveFiles = [];
        foreach ($sensitivePaths as $idx => $path) {
            $resp = $responses[$idx];
            $sensitiveFiles[$path] = $resp instanceof \Illuminate\Http\Client\Response
                ? $resp->status() === 200
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
        $robotsTxt = $responses[$robotsIdx] instanceof \Illuminate\Http\Client\Response && $responses[$robotsIdx]->ok();

        $sitemapIdx = $robotsIdx + 1;
        $sitemapXml = $responses[$sitemapIdx] instanceof \Illuminate\Http\Client\Response && $responses[$sitemapIdx]->ok();
        $securityTxtIdx = $sitemapIdx + 1;
        $securityTxt = $responses[$securityTxtIdx] instanceof \Illuminate\Http\Client\Response && $responses[$securityTxtIdx]->ok();

        return [$sensitiveFiles, $directoryListing, $robotsTxt, $sitemapXml, $securityTxt];
    }
}

