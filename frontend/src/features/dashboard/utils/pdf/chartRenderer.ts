import { COLORS } from "./constants";
import { getScoreColor } from "./helpers";
import type { PdfScores } from "./types";

export const renderScoresChart = async (scores: PdfScores): Promise<string | null> => {
  const ChartModule = await import("chart.js/auto");
  const Chart = ChartModule.default;

  const canvas = document.createElement("canvas");
  canvas.width = 900;
  canvas.height = 450;
  canvas.style.display = "none";
  document.body.appendChild(canvas);

  const labels = ["Производительность", "Доступность", "Стандарты", "SEO"];
  const values = [
    scores.performance ?? 0,
    scores.accessibility ?? 0,
    scores.bestPractices ?? 0,
    scores.seo ?? 0,
  ];
  const colors = values.map((v) => getScoreColor(v));

  try {
    const ctx = canvas.getContext("2d");
    if (!ctx) return null;

    ctx.fillStyle = "#ffffff";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    const chart = new Chart(ctx, {
      type: "bar",
      data: {
        labels,
        datasets: [
          {
            label: "Оценка",
            data: values,
            backgroundColor: colors,
            borderColor: colors,
            borderWidth: 1,
            borderRadius: 6,
          },
        ],
      },
      options: {
        responsive: false,
        animation: false,
        plugins: {
          legend: { display: false },
          tooltip: { enabled: false },
          title: {
            display: true,
            text: "Оценки Lighthouse (из 100)",
            color: COLORS.text,
            font: { size: 20, weight: "bold" },
            padding: { top: 10, bottom: 20 },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: {
              color: COLORS.text,
              font: { size: 14 },
              stepSize: 25,
            },
            grid: { color: "rgba(0,0,0,0.06)" },
          },
          x: {
            ticks: {
              color: COLORS.text,
              font: { size: 14, weight: "bold" },
            },
            grid: { display: false },
          },
        },
      },
      plugins: [
        {
          id: "valueLabels",
          afterDatasetsDraw(chart) {
            const { ctx } = chart;
            ctx.save();
            ctx.font = "bold 16px sans-serif";
            ctx.fillStyle = COLORS.text;
            ctx.textAlign = "center";
            chart.data.datasets.forEach((_, i) => {
              const meta = chart.getDatasetMeta(i);
              meta.data.forEach((bar, index) => {
                const value = values[index];
                const point = (
                  bar as unknown as { getCenterPoint(): { x: number; y: number } }
                ).getCenterPoint();
                ctx.fillText(String(value), point.x, point.y - 10);
              });
            });
            ctx.restore();
          },
        },
      ],
    });

    await new Promise((r) => setTimeout(r, 50));
    const dataUrl = canvas.toDataURL("image/png");
    chart.destroy();
    return dataUrl;
  } finally {
    canvas.remove();
  }
};
