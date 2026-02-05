import { computed } from 'vue';
import type { AuditRecommendation } from '@/features/history/types';

export function useRecommendations(recommendations: AuditRecommendation[]) {
  const categories = computed(() => {
    const counts = {
      all: recommendations.length,
      performance: 0,
      accessibility: 0,
      'best-practices': 0,
      seo: 0,
    };

    recommendations.forEach(rec => {
      if (rec.category in counts) {
        counts[rec.category as keyof typeof counts]++;
      }
    });

    return [
      { key: 'all', label: 'Все', count: counts.all },
      { key: 'performance', label: 'Performance', count: counts.performance },
      { key: 'accessibility', label: 'Accessibility', count: counts.accessibility },
      { key: 'best-practices', label: 'Best Practices', count: counts['best-practices'] },
      { key: 'seo', label: 'SEO', count: counts.seo },
    ];
  });

  const getCategoryLabel = (category: string): string => {
    const cat = categories.value.find(c => c.key === category);
    return cat?.label || category;
  };

  const filterByCategory = (category: string) => {
    if (category === 'all') return recommendations;
    return recommendations.filter(r => r.category === category);
  };

  const formatBytes = (bytes: number): string => {
    if (bytes < 1024) return `${bytes}B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)}KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)}MB`;
  };

  return {
    categories,
    getCategoryLabel,
    filterByCategory,
    formatBytes,
  };
}
