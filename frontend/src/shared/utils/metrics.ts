export type MetricStatus = "good" | "needs-improvement" | "poor" | "unknown";

export const getMetricStatus = (
  metric: string,
  value: number | null
): MetricStatus => {
  if (value === null) return "unknown";

  switch (metric) {
    case "lcp":
      if (value < 2.5) return "good";
      if (value <= 4.0) return "needs-improvement";
      return "poor";

    case "fid":
      // INP: good < 200ms, needs-improvement <= 500ms
      // TBT (fallback): good < 200ms, needs-improvement <= 600ms
      if (value < 200) return "good";
      if (value <= 500) return "needs-improvement";
      return "poor";

    case "cls":
      if (value < 0.1) return "good";
      if (value <= 0.25) return "needs-improvement";
      return "poor";

    default:
      return "unknown";
  }
};

