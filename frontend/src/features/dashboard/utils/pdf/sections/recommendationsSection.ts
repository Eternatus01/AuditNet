/* eslint-disable @typescript-eslint/no-explicit-any */
import { CATEGORY_LABELS, RECOMMENDATION_GROUP_ORDER } from "../constants";
import { recommendationsMap } from "../../recommendationsTranslations";
import { recommendationCardLayout } from "../tableLayouts";
import type { PdfRecommendation } from "../types";

const translateRecommendation = (rec: PdfRecommendation) => {
  const translation = recommendationsMap[rec.audit_id_key];
  return {
    title: translation?.title || rec.title || rec.audit_id_key,
    solution: translation?.solution || rec.description || null,
  };
};

export const buildRecommendationsSection = (
  recommendations: PdfRecommendation[],
): any[] => {
  const grouped: Record<string, PdfRecommendation[]> = {};
  for (const rec of recommendations) {
    const cat = rec.category || "other";
    if (!grouped[cat]) grouped[cat] = [];
    grouped[cat].push(rec);
  }

  const categoryKeys = Object.keys(grouped).sort((a, b) => {
    const ai = RECOMMENDATION_GROUP_ORDER.indexOf(a);
    const bi = RECOMMENDATION_GROUP_ORDER.indexOf(b);
    if (ai === -1 && bi === -1) return a.localeCompare(b);
    if (ai === -1) return 1;
    if (bi === -1) return -1;
    return ai - bi;
  });

  const blocks: any[] = [
    {
      unbreakable: true,
      margin: [0, 12, 0, 0],
      stack: [
        { text: "Рекомендации по улучшению", style: "sectionTitle" },
        {
          text: `Всего найдено проблем: ${recommendations.length}`,
          style: "muted",
          margin: [0, 0, 0, 10],
        },
      ],
    },
  ];

  for (const cat of categoryKeys) {
    const list = grouped[cat];

    blocks.push({
      unbreakable: true,
      margin: [0, 10, 0, 6],
      stack: [
        {
          text: `${CATEGORY_LABELS[cat] || cat} (${list.length})`,
          style: "subsectionTitle",
        },
      ],
    });

    for (const rec of list) {
      const { title, solution } = translateRecommendation(rec);

      blocks.push({
        unbreakable: true,
        margin: [0, 0, 0, 6],
        table: {
          widths: ["*"],
          body: [
            [
              {
                stack: [
                  { text: title, style: "recTitle" },
                  rec.display_value
                    ? { text: `Показатель: ${rec.display_value}`, style: "recValue" }
                    : null,
                  solution
                    ? { text: solution, style: "recSolution", margin: [0, 4, 0, 0] }
                    : null,
                ].filter(Boolean),
              },
            ],
          ],
        },
        layout: recommendationCardLayout,
      });
    }
  }

  return blocks;
};
