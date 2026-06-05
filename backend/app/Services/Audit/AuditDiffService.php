<?php

namespace App\Services\Audit;

use App\Enums\AuditStatus;
use App\Models\Audit;

class AuditDiffService
{
    private const SCORE_LABELS = [
        'performance' => 'Производительность',
        'accessibility' => 'Доступность',
        'best_practices' => 'Лучшие практики',
        'seo' => 'SEO',
    ];

    private const METRIC_META = [
        'lcp' => ['label' => 'LCP (отрисовка контента)', 'unit' => 's'],
        'fcp' => ['label' => 'FCP (первая отрисовка)', 'unit' => 's'],
        'speed_index' => ['label' => 'Speed Index', 'unit' => 's'],
        'tbt' => ['label' => 'TBT (блокировка)', 'unit' => 'ms'],
        'fid' => ['label' => 'Интерактивность', 'unit' => 'ms'],
        'cls' => ['label' => 'CLS (сдвиги макета)', 'unit' => 'cls'],
    ];

    public function diff(Audit $current): array
    {
        $previous = $this->findPreviousAudit($current);

        if (!$previous) {
            return [
                'has_previous' => false,
                'audit_id' => $current->id,
                'previous_audit_id' => null,
                'score_deltas' => [],
                'metric_deltas' => [],
                'explanations' => [],
            ];
        }

        $scoreDeltas = $this->buildScoreDeltas($current, $previous);
        $metricDeltas = $this->buildMetricDeltas($current, $previous);
        $explanations = $this->buildExplanations($current, $previous, $scoreDeltas, $metricDeltas);

        return [
            'has_previous' => true,
            'audit_id' => $current->id,
            'previous_audit_id' => $previous->id,
            'previous_audited_at' => $previous->audited_at?->toIso8601String(),
            'score_deltas' => $scoreDeltas,
            'metric_deltas' => $metricDeltas,
            'explanations' => $explanations,
        ];
    }

    private function findPreviousAudit(Audit $current): ?Audit
    {
        $query = Audit::query()
            ->where('status', AuditStatus::COMPLETED)
            ->where('id', '!=', $current->id)
            ->where('id', '<', $current->id);

        if ($current->monitored_site_id) {
            $query->where('monitored_site_id', $current->monitored_site_id);
        } else {
            $query->where('user_id', $current->user_id)
                ->where('url', $current->url);
        }

        return $query->with('recommendations')->orderByDesc('id')->first();
    }

    private function buildScoreDeltas(Audit $current, Audit $previous): array
    {
        $deltas = [];

        foreach (array_keys(self::SCORE_LABELS) as $key) {
            $cur = $current->{$key};
            $prev = $previous->{$key};

            if ($cur === null || $prev === null) {
                continue;
            }

            $deltas[$key] = [
                'current' => (int) $cur,
                'previous' => (int) $prev,
                'delta' => (int) $cur - (int) $prev,
            ];
        }

        return $deltas;
    }

    private function buildMetricDeltas(Audit $current, Audit $previous): array
    {
        $deltas = [];

        foreach (self::METRIC_META as $key => $meta) {
            $cur = $current->{$key};
            $prev = $previous->{$key};

            if ($cur === null || $prev === null) {
                continue;
            }

            $deltas[$key] = [
                'current' => (float) $cur,
                'previous' => (float) $prev,
                'delta' => round((float) $cur - (float) $prev, 4),
                'unit' => $meta['unit'],
            ];
        }

        return $deltas;
    }

    private function buildExplanations(Audit $current, Audit $previous, array $scoreDeltas, array $metricDeltas): array
    {
        $explanations = [];

        foreach ($scoreDeltas as $key => $data) {
            if ($data['delta'] === 0) {
                continue;
            }

            $label = self::SCORE_LABELS[$key];
            $abs = abs($data['delta']);

            if ($data['delta'] > 0) {
                $explanations[] = "{$label}: +{$data['delta']} (было {$data['previous']}, стало {$data['current']}). Показатель улучшился.";
            } else {
                $text = "{$label}: {$data['delta']} (было {$data['previous']}, стало {$data['current']}). Показатель снизился на {$abs}.";

                if ($key === 'performance') {
                    $reasons = $this->performanceRegressionReasons($metricDeltas);
                    if ($reasons) {
                        $text .= ' Вероятная причина: ' . $reasons . '.';
                    }
                }

                $explanations[] = $text;
            }
        }

        $recExplanations = $this->recommendationChanges($current, $previous);

        return array_merge($explanations, $recExplanations);
    }

    private function performanceRegressionReasons(array $metricDeltas): string
    {
        $reasons = [];

        foreach ($metricDeltas as $key => $data) {
            if ($data['delta'] <= 0) {
                continue;
            }

            $meta = self::METRIC_META[$key] ?? null;
            if (!$meta) {
                continue;
            }

            $from = $this->formatMetric($data['previous'], $meta['unit']);
            $to = $this->formatMetric($data['current'], $meta['unit']);
            $reasons[] = "{$meta['label']} вырос с {$from} до {$to}";
        }

        return implode(', ', $reasons);
    }

    private function recommendationChanges(Audit $current, Audit $previous): array
    {
        $explanations = [];

        $currentRecs = $current->recommendations->keyBy('audit_id_key');
        $previousRecs = $previous->recommendations->keyBy('audit_id_key');

        $newKeys = $currentRecs->keys()->diff($previousRecs->keys());
        $resolvedKeys = $previousRecs->keys()->diff($currentRecs->keys());

        if ($newKeys->isNotEmpty()) {
            $titles = $newKeys->take(5)->map(fn ($key) => $currentRecs[$key]->title)->implode(', ');
            $explanations[] = "Появились новые проблемы: {$titles}.";
        }

        if ($resolvedKeys->isNotEmpty()) {
            $titles = $resolvedKeys->take(5)->map(fn ($key) => $previousRecs[$key]->title)->implode(', ');
            $explanations[] = "Исправлено по сравнению с прошлым разом: {$titles}.";
        }

        return $explanations;
    }

    private function formatMetric(float $value, string $unit): string
    {
        return match ($unit) {
            's' => number_format($value, 2, '.', '') . ' с',
            'ms' => number_format($value, 0, '.', '') . ' мс',
            'cls' => number_format($value, 3, '.', ''),
            default => (string) $value,
        };
    }
}
