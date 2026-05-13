/* eslint-disable @typescript-eslint/no-explicit-any */
import { renderScoresChart } from "./chartRenderer";
import { extractDomain, sanitizeFilename } from "./helpers";
import { buildTitleSection } from "./sections/titleSection";
import { buildScoresSection } from "./sections/scoresSection";
import { buildVitalsSection } from "./sections/vitalsSection";
import { buildSecuritySection } from "./sections/securitySection";
import { buildRecommendationsSection } from "./sections/recommendationsSection";
import { defaultPdfDefaultStyle, defaultPdfStyles } from "./styles";
import type { AuditReportData } from "./types";

const loadPdfMake = async () => {
  const [pdfMakeModule, vfsModule] = await Promise.all([
    import("pdfmake/build/pdfmake"),
    import("pdfmake/build/vfs_fonts"),
  ]);

  const pdfMake: any =
    (pdfMakeModule as any).default || (pdfMakeModule as any).pdfMake || pdfMakeModule;
  const vfsExport: any = (vfsModule as any).default || vfsModule;
  const vfs = vfsExport?.pdfMake?.vfs || vfsExport?.vfs || vfsExport;
  pdfMake.vfs = vfs;

  return pdfMake;
};

const buildHeader =
  (domain: string) =>
  (currentPage: number): any => {
    if (currentPage === 1) return null;
    return {
      columns: [
        {
          text: "AuditNet — Отчёт аудита",
          style: "runningHeader",
          margin: [40, 20, 0, 0],
        },
        {
          text: domain,
          style: "runningHeader",
          alignment: "right",
          margin: [0, 20, 40, 0],
        },
      ],
    };
  };

const buildFooter = (currentPage: number, pageCount: number): any => ({
  columns: [
    {
      text: "AuditNet · аудит производительности и безопасности",
      style: "footer",
      margin: [40, 0, 0, 0],
    },
    {
      text: `Страница ${currentPage} из ${pageCount}`,
      style: "footer",
      alignment: "right",
      margin: [0, 0, 40, 0],
    },
  ],
});

export const generateAuditPdf = async (data: AuditReportData): Promise<void> => {
  const pdfMake = await loadPdfMake();
  const chartImage = await renderScoresChart(data.scores).catch(() => null);

  const domain = extractDomain(data.url);

  const content: any[] = [
    buildTitleSection(data.url, data.auditedAt),
    buildScoresSection(data.scores),
  ];

  if (chartImage) {
    content.push({
      image: chartImage,
      width: 500,
      alignment: "center",
      margin: [0, 16, 0, 16],
    });
  }

  content.push(buildVitalsSection(data.vitals));

  if (data.securityAudit) {
    content.push(...buildSecuritySection(data.securityAudit));
  }

  if (data.recommendations && data.recommendations.length > 0) {
    content.push(...buildRecommendationsSection(data.recommendations));
  }

  const docDefinition: any = {
    pageSize: "A4",
    pageMargins: [40, 60, 40, 50],
    info: {
      title: `AuditNet — отчёт ${domain}`,
      author: "AuditNet",
      subject: "Отчёт аудита производительности и безопасности сайта",
      keywords: "audit, lighthouse, performance, security",
    },
    header: buildHeader(domain),
    footer: buildFooter,
    content,
    defaultStyle: defaultPdfDefaultStyle,
    styles: defaultPdfStyles,
  };

  const fileName = `auditnet-report-${sanitizeFilename(domain)}-${new Date()
    .toISOString()
    .slice(0, 10)}.pdf`;

  return new Promise<void>((resolve, reject) => {
    try {
      pdfMake.createPdf(docDefinition).download(fileName, () => resolve());
    } catch (e) {
      reject(e);
    }
  });
};
