export type MetricStatus = "good" | "needs-improvement" | "poor" | "unknown";

export const getMetricStatus = (
  metric: string,
  value: number | null
): MetricStatus => {
  if (value === null) return "unknown";

  switch (metric) {
    case "lcp":
      // LCP: < 2.5s = good, 2.5-4s = needs-improvement, > 4s = poor
      if (value < 2.5) return "good";
      if (value <= 4.0) return "needs-improvement";
      return "poor";

    case "fid":
      // FID: < 100ms = good, 100-300ms = needs-improvement, > 300ms = poor
      if (value < 100) return "good";
      if (value <= 300) return "needs-improvement";
      return "poor";

    case "cls":
      // CLS: < 0.1 = good, 0.1-0.25 = needs-improvement, > 0.25 = poor
      if (value < 0.1) return "good";
      if (value <= 0.25) return "needs-improvement";
      return "poor";

    default:
      return "unknown";
  }
};

