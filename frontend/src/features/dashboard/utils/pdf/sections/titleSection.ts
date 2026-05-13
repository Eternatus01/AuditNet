/* eslint-disable @typescript-eslint/no-explicit-any */
import { COLORS } from "../constants";
import { formatDate } from "../helpers";

export const buildTitleSection = (url: string, auditedAt?: string | null): any => ({
  style: "hero",
  table: {
    widths: ["*"],
    body: [
      [
        {
          stack: [
            { text: "AuditNet", style: "brand" },
            { text: "Отчёт аудита сайта", style: "heroTitle", margin: [0, 4, 0, 8] },
            { text: url, style: "heroUrl" },
            {
              text: `Дата проверки: ${formatDate(auditedAt)}`,
              style: "heroDate",
              margin: [0, 4, 0, 0],
            },
          ],
        },
      ],
    ],
  },
  layout: {
    fillColor: () => COLORS.primary,
    hLineWidth: () => 0,
    vLineWidth: () => 0,
    paddingTop: () => 20,
    paddingBottom: () => 20,
    paddingLeft: () => 24,
    paddingRight: () => 24,
  },
  margin: [0, 0, 0, 20],
});
