/* eslint-disable @typescript-eslint/no-explicit-any */
import { getScoreColor, getScoreLabel } from "../helpers";
import { stripedTableLayout } from "../tableLayouts";
import type { PdfScores } from "../types";

export const buildScoresSection = (scores: PdfScores): any => {
  const rows: Array<[string, number | null]> = [
    ["Производительность", scores.performance],
    ["Доступность", scores.accessibility],
    ["Стандарты качества", scores.bestPractices],
    ["SEO", scores.seo],
  ];

  return {
    style: "section",
    unbreakable: true,
    stack: [
      { text: "Основные оценки", style: "sectionTitle" },
      {
        table: {
          headerRows: 1,
          widths: ["*", 80, 140],
          body: [
            [
              { text: "Категория", style: "tableHeader" },
              { text: "Оценка", style: "tableHeader", alignment: "center" },
              { text: "Статус", style: "tableHeader", alignment: "center" },
            ],
            ...rows.map(([label, value]) => [
              { text: label, style: "tableCell" },
              {
                text: value === null || value === undefined ? "—" : String(value),
                style: "tableCellBold",
                alignment: "center",
                color: getScoreColor(value),
              },
              {
                text: getScoreLabel(value),
                style: "tableCell",
                alignment: "center",
                color: getScoreColor(value),
              },
            ]),
          ],
        },
        layout: stripedTableLayout,
      },
    ],
  };
};
