import pdfMake from "pdfmake/build/pdfmake";
import pdfFonts from "pdfmake/build/vfs_fonts";
import type { TDocumentDefinitions } from "pdfmake/interfaces";
import type { AuditComparison } from "@/features/history/types";
import { comparisonMetrics, getAverageScore, getHostname } from "./comparisonHelpers";
import { mapComparisonSiteToAuditData } from "./mappers";

pdfMake.vfs = pdfFonts.vfs;

export const generateComparisonPdf = async (comparison: AuditComparison): Promise<void> => {
  const successfulSites = comparison.sites
    .map((site) => ({ site, data: mapComparisonSiteToAuditData(site) }))
    .filter((item): item is { site: typeof item.site; data: NonNullable<typeof item.data> } => item.data !== null);

  const metricRows = comparisonMetrics.map((metric) => [
    { text: metric.label, bold: true },
    ...successfulSites.map(({ data }) => metric.format(metric.getValue(data), data)),
  ]);

  const docDefinition: TDocumentDefinitions = {
    pageSize: "A4",
    pageMargins: [36, 42, 36, 42],
    content: [
      { text: "Отчёт сравнения сайтов", style: "title" },
      { text: comparison.title || "Сравнение сайтов", style: "subtitle" },
      { text: `Дата: ${new Date(comparison.audited_at || comparison.created_at).toLocaleString("ru-RU")}`, margin: [0, 0, 0, 16] },
      {
        columns: successfulSites.map(({ site, data }) => ({
          width: "*",
          stack: [
            { text: getHostname(site.url), bold: true },
            { text: site.url, fontSize: 8, color: "#555" },
            { text: `Средняя оценка: ${Math.round(getAverageScore(data) ?? 0)}`, margin: [0, 6, 0, 0] },
          ],
        })),
        columnGap: 10,
        margin: [0, 0, 0, 16],
      },
      {
        table: {
          headerRows: 1,
          widths: ["*", ...successfulSites.map(() => "auto")],
          body: [
            [
              { text: "Показатель", bold: true },
              ...successfulSites.map(({ site }) => ({ text: getHostname(site.url), bold: true })),
            ],
            ...metricRows,
          ],
        },
        layout: "lightHorizontalLines",
      },
      ...(comparison.sites.some((site) => site.error_message)
        ? [
            { text: "Ошибки анализа", style: "sectionTitle", margin: [0, 18, 0, 8] },
            {
              ul: comparison.sites
                .filter((site) => site.error_message)
                .map((site) => `${site.url}: ${site.error_message}`),
            },
          ]
        : []),
    ],
    styles: {
      title: {
        fontSize: 22,
        bold: true,
        margin: [0, 0, 0, 8],
      },
      subtitle: {
        fontSize: 14,
        color: "#555",
        margin: [0, 0, 0, 8],
      },
      sectionTitle: {
        fontSize: 14,
        bold: true,
      },
    },
    defaultStyle: {
      fontSize: 10,
    },
  };

  const fileName = `comparison-report-${comparison.id}.pdf`;
  pdfMake.createPdf(docDefinition).download(fileName);
};
