<?php

namespace App\Repositories;

use App\Enums\AuditStatus;
use App\Models\AuditComparison;
use App\Models\User;
use Illuminate\Support\Str;

class AuditComparisonRepository
{
    public function createPending(array $urls, User $user): AuditComparison
    {
        return AuditComparison::create([
            'user_id' => $user->id,
            'title' => $this->makeTitle($urls),
            'status' => AuditStatus::PENDING,
        ]);
    }

    public function findByIdForUserOrFail(int $id, User $user): AuditComparison
    {
        return $user->auditComparisons()->with('sites')->findOrFail($id);
    }

    public function findByShareTokenOrFail(string $token): AuditComparison
    {
        return AuditComparison::with('sites')
            ->where('share_token', $token)
            ->firstOrFail();
    }

    public function ensureShareToken(AuditComparison $comparison): string
    {
        if ($comparison->share_token) {
            return $comparison->share_token;
        }

        do {
            $token = Str::random(48);
        } while (AuditComparison::where('share_token', $token)->exists());

        $comparison->update(['share_token' => $token]);

        return $token;
    }

    private function makeTitle(array $urls): string
    {
        $hosts = array_map(function (string $url): string {
            return preg_replace('/^www\./', '', parse_url($url, PHP_URL_HOST) ?: $url);
        }, $urls);

        return implode(' vs ', array_slice($hosts, 0, 3)) . (count($hosts) > 3 ? ' +' . (count($hosts) - 3) : '');
    }
}
