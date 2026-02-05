<?php

namespace App\Repositories;

use App\Models\Audit;
use App\Models\User;
use App\Enums\AuditStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditRepository
{
    public function findById(int $id): ?Audit
    {
        return Audit::with(['securityAudit', 'recommendations'])->find($id);
    }

    public function findByIdForUser(int $id, User $user): ?Audit
    {
        return $user->audits()->with(['securityAudit', 'recommendations'])->find($id);
    }

    public function findByIdForUserOrFail(int $id, User $user): Audit
    {
        return $user->audits()->with(['securityAudit', 'recommendations'])->findOrFail($id);
    }

    public function getUserAuditsPaginated(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $user->audits()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function createPendingAudit(string $url, int $userId): Audit
    {
        return Audit::create([
            'user_id' => $userId,
            'url' => $url,
            'status' => AuditStatus::PENDING,
        ]);
    }

    public function updateAuditStatus(int $auditId, AuditStatus $status, array $data = []): bool
    {
        $audit = $this->findById($auditId);
        
        if (!$audit) {
            return false;
        }

        return $audit->update(array_merge(['status' => $status], $data));
    }

    public function updateAuditWithResults(int $auditId, array $results): bool
    {
        $audit = $this->findById($auditId);
        
        if (!$audit) {
            return false;
        }

        return $audit->update([
            'status' => AuditStatus::COMPLETED,
            'performance' => $results['performance'],
            'accessibility' => $results['accessibility'],
            'best_practices' => $results['bestPractices'],
            'seo' => $results['seo'],
            'lcp' => $results['lcp'],
            'fid' => $results['fid'],
            'cls' => $results['cls'],
            'fcp' => $results['fcp'],
            'tbt' => $results['tbt'],
            'speed_index' => $results['speedIndex'],
            'audited_at' => now(),
        ]);
    }

    public function markAuditAsFailed(int $auditId, string $errorMessage): bool
    {
        $audit = $this->findById($auditId);
        
        if (!$audit) {
            return false;
        }

        return $audit->update([
            'status' => AuditStatus::FAILED,
            'error_message' => $errorMessage,
        ]);
    }

}

