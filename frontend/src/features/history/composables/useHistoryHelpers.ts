export const useHistoryHelpers = () => {
  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat("ru-RU", {
      year: "numeric",
      month: "long",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    }).format(date);
  };

  const getScoreClass = (score: number | null) => {
    if (score == null) return "unknown";
    if (score >= 90) return "good";
    if (score >= 50) return "average";
    return "poor";
  };

  const scorePercent = (score: number | string | null) => {
    if (score === null || score === undefined) return 0;
    const n = typeof score === "string" ? parseFloat(score) : score;
    if (Number.isNaN(n)) return 0;
    return Math.max(0, Math.min(100, n));
  };

  return {
    formatDate,
    getScoreClass,
    scorePercent,
  };
};

