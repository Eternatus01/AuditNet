<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventSsrfAttacks
{
    protected array $blockedIpRanges = [
        '127.0.0.0/8',      // localhost
        '10.0.0.0/8',       // Private network A
        '172.16.0.0/12',    // Private network B
        '192.168.0.0/16',   // Private network C
        '169.254.0.0/16',   // Link-local
        '::1/128',          // IPv6 localhost
        'fc00::/7',         // IPv6 private
        '0.0.0.0/8',        // "This" network
        '100.64.0.0/10',    // Shared address space
    ];

    protected array $blockedDomains = [
        'localhost',
        'metadata.google.internal',
        '169.254.169.254',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $urls = $request->input('urls');
        if (is_array($urls)) {
            foreach ($urls as $url) {
                if (!is_string($url) || $url === '') {
                    continue;
                }
                $blockedResponse = $this->validateUrl($url);
                if ($blockedResponse) {
                    return $blockedResponse;
                }
            }

            return $next($request);
        }

        $url = $request->input('url');

        if (!$url || !is_string($url)) {
            return $next($request);
        }

        $blockedResponse = $this->validateUrl($url);
        if ($blockedResponse) {
            return $blockedResponse;
        }

        return $next($request);
    }

    private function validateUrl(string $url): ?Response
    {
        if (!$this->isValidUrl($url)) {
            return response()->json([
                'success' => false,
                'message' => 'Некорректный URL формат',
            ], 400);
        }

        if ($this->isSsrfAttempt($url)) {
            return response()->json([
                'success' => false,
                'message' => 'Данный URL запрещен из соображений безопасности',
            ], 403);
        }

        return null;
    }

    protected function isValidUrl(string $url): bool
    {
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['scheme'], $parsed['host']) || $parsed['host'] === '') {
            return false;
        }

        if (!in_array(strtolower($parsed['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $asciiUrl = $this->urlWithAsciiHost($url, $parsed);

        if (filter_var($asciiUrl, FILTER_VALIDATE_URL)) {
            return true;
        }

        if ($asciiUrl === $url && preg_match('/[^\x00-\x7F]/', $parsed['host'])) {
            return (bool) preg_match('/^[\p{L}\p{N}\p{M}.-]+$/u', $parsed['host']);
        }

        return false;
    }

    private function urlWithAsciiHost(string $url, ?array $parsed = null): string
    {
        $parsed ??= parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            return $url;
        }

        $host = $parsed['host'];
        if (str_starts_with($host, '[')) {
            return $url;
        }

        if (!function_exists('idn_to_ascii')) {
            return $url;
        }

        $ascii = @idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        if ($ascii === false || $ascii === $host) {
            return $url;
        }

        $parsed['host'] = $ascii;

        return $this->buildUrlFromParts($parsed);
    }

    private function buildUrlFromParts(array $parts): string
    {
        $scheme = ($parts['scheme'] ?? 'http') . '://';
        $result = $scheme;

        if (isset($parts['user']) || isset($parts['pass'])) {
            $result .= ($parts['user'] ?? '');
            if (isset($parts['pass'])) {
                $result .= ':' . $parts['pass'];
            }
            $result .= '@';
        }

        $result .= $parts['host'] ?? '';
        if (isset($parts['port'])) {
            $result .= ':' . $parts['port'];
        }
        $result .= $parts['path'] ?? '';
        if (isset($parts['query'])) {
            $result .= '?' . $parts['query'];
        }
        if (isset($parts['fragment'])) {
            $result .= '#' . $parts['fragment'];
        }

        return $result;
    }

    protected function isSsrfAttempt(string $url): bool
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';

        if ($host !== '' && !str_starts_with($host, '[') && function_exists('idn_to_ascii')) {
            $ascii = @idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if ($ascii !== false) {
                $host = $ascii;
            }
        }

        if (in_array(strtolower($host), $this->blockedDomains)) {
            return true;
        }

        $ip = gethostbyname($host);
        if ($ip === $host) {
            return false;
        }

        foreach ($this->blockedIpRanges as $range) {
            if ($this->ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    protected function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, ':') !== false) {
            return $this->ipv6InRange($ip, $range);
        }

        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);

        if ($ip === false || $subnet === false) {
            return false;
        }

        $mask = -1 << (32 - (int) $bits);
        $subnet &= $mask;

        return ($ip & $mask) === $subnet;
    }

    protected function ipv6InRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range);

        $ip = inet_pton($ip);
        $subnet = inet_pton($subnet);

        if ($ip === false || $subnet === false) {
            return false;
        }

        $bitsInt = (int) $bits;
        $bytesToCheck = (int) ($bitsInt / 8);
        $bitsInLastByte = $bitsInt % 8;

        for ($i = 0; $i < $bytesToCheck; $i++) {
            if ($ip[$i] !== $subnet[$i]) {
                return false;
            }
        }

        if ($bitsInLastByte > 0) {
            $mask = 0xFF << (8 - $bitsInLastByte);
            if ((ord($ip[$bytesToCheck]) & $mask) !== (ord($subnet[$bytesToCheck]) & $mask)) {
                return false;
            }
        }

        return true;
    }
}
