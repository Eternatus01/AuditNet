<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\StoreMonitoredSiteRequest;
use App\Http\Requests\UpdateMonitoredSiteRequest;
use App\Http\Resources\MonitoredSiteResource;
use App\Jobs\RunSiteAuditJob;
use App\Models\MonitoredSite;
use Illuminate\Http\JsonResponse;

class SiteController extends BaseApiController
{
    private const MAX_SITES = 3;

    public function index(): JsonResponse
    {
        $user = $this->requireAuthenticatedUser();

        $sites = $user->monitoredSites()
            ->with('lastAudit')
            ->orderBy('created_at')
            ->get();

        return $this->successResponse(MonitoredSiteResource::collection($sites)->resolve());
    }

    public function store(StoreMonitoredSiteRequest $request): JsonResponse
    {
        $user = $this->requireAuthenticatedUser();

        if ($user->monitoredSites()->count() >= self::MAX_SITES) {
            return $this->errorResponse('Можно добавить не более ' . self::MAX_SITES . ' сайтов.', 422);
        }

        $site = $user->monitoredSites()->create([
            'url' => $request->input('url'),
            'name' => $request->input('name'),
            'schedule_day' => $request->input('schedule_day'),
            'is_active' => true,
        ]);

        return $this->successResponse(
            (new MonitoredSiteResource($site))->resolve(),
            'Сайт добавлен.',
            201
        );
    }

    public function update(UpdateMonitoredSiteRequest $request, int $id): JsonResponse
    {
        $user = $this->requireAuthenticatedUser();

        $site = $user->monitoredSites()->find($id);

        if (!$site) {
            return $this->errorResponse('Сайт не найден.', 404);
        }

        $site->update($request->only(['name', 'schedule_day', 'is_active']));

        return $this->successResponse(
            (new MonitoredSiteResource($site->fresh('lastAudit')))->resolve(),
            'Сайт обновлён.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $user = $this->requireAuthenticatedUser();

        $site = $user->monitoredSites()->find($id);

        if (!$site) {
            return $this->errorResponse('Сайт не найден.', 404);
        }

        $site->delete();

        return $this->successResponse(null, 'Сайт удалён.');
    }

    public function run(int $id): JsonResponse
    {
        $user = $this->requireAuthenticatedUser();

        $site = $user->monitoredSites()->find($id);

        if (!$site) {
            return $this->errorResponse('Сайт не найден.', 404);
        }

        RunSiteAuditJob::dispatch($site);

        return $this->successResponse(null, 'Анализ запущен. Результат появится через несколько минут.', 202);
    }
}
