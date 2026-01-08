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
        $url = $request->input('url');

        if (!$url) {
            return $next($request);
        }

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

        return $next($request);
    }

    protected function isValidUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['scheme']) || !isset($parsed['host'])) {
            return false;
        }

        if (!in_array($parsed['scheme'], ['http', 'https'])) {
            return false;
        }

        return true;
    }

    protected function isSsrfAttempt(string $url): bool
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';

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
        
        $mask = -1 << (32 - (int)$bits);
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
        
        $bitsInt = (int)$bits;
        $bytesToCheck = (int)($bitsInt / 8);
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

