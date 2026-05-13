/* eslint-disable @typescript-eslint/no-explicit-any */
import { COLORS, SECURITY_HEADER_LABELS } from "../constants";
import { compactStripedTableLayout, plainStripedTableLayout } from "../tableLayouts";
import type { PdfSecurityAudit } from "../types";

const buildHeadersTable = (security: PdfSecurityAudit): any => {
  const body: any[] = [
    [
      { text: "Заголовок", style: "tableHeader" },
      { text: "Статус", style: "tableHeader", alignment: "center" },
    ],
  ];

  const entries = Object.entries(security.headers || {});
  if (entries.length === 0) {
    body.push([
      {
        text: "Нет данных о заголовках",
        style: "tableCellMuted",
        colSpan: 2,
        alignment: "center",
      },
      {},
    ]);
  } else {
    for (const [key, value] of entries) {
      const present = Boolean(value);
      const label = SECURITY_HEADER_LABELS[key.toLowerCase()] || key;
      body.push([
        { text: label, style: "tableCell" },
        {
          text: present ? "Присутствует" : "Отсутствует",
          style: "tableCellBold",
          alignment: "center",
          color: present ? COLORS.good : COLORS.poor,
        },
      ]);
    }
  }

  return {
    table: { headerRows: 1, widths: ["*", 140], body },
    layout: compactStripedTableLayout,
  };
};

const buildSensitiveFilesTable = (security: PdfSecurityAudit): any => {
  const entries = Object.entries(security.sensitive_files || {});
  const body: any[] = [
    [
      { text: "Файл / путь", style: "tableHeader" },
      { text: "Доступность", style: "tableHeader", alignment: "center" },
    ],
  ];

  if (entries.length === 0) {
    body.push([
      {
        text: "Чувствительные файлы не проверялись",
        style: "tableCellMuted",
        colSpan: 2,
        alignment: "center",
      },
      {},
    ]);
  } else {
    for (const [file, isExposed] of entries) {
      body.push([
        { text: file, style: "tableCell" },
        {
          text: isExposed ? "Доступен (риск!)" : "Недоступен",
          style: "tableCellBold",
          alignment: "center",
          color: isExposed ? COLORS.poor : COLORS.good,
        },
      ]);
    }
  }

  return {
    table: { headerRows: 1, widths: ["*", 140], body },
    layout: compactStripedTableLayout,
  };
};

const buildAdditionalTable = (security: PdfSecurityAudit): any => {
  const directoryEntries = Object.entries(security.directory_listing || {});
  const directoryIssues = directoryEntries.filter(([, v]) => v);

  const directoryText =
    directoryEntries.length === 0
      ? "Проверка не выполнялась"
      : directoryIssues.length === 0
        ? "Listing закрыт для всех проверенных путей"
        : `Открытый listing: ${directoryIssues.map(([k]) => k).join(", ")}`;

  const directoryColor =
    directoryEntries.length === 0
      ? COLORS.muted
      : directoryIssues.length === 0
        ? COLORS.good
        : COLORS.poor;

  return {
    table: {
      widths: ["*", 140],
      body: [
        [
          { text: "robots.txt", style: "tableCell" },
          {
            text: security.robots_txt ? "Найден" : "Не найден",
            style: "tableCellBold",
            alignment: "center",
            color: security.robots_txt ? COLORS.good : COLORS.moderate,
          },
        ],
        [
          { text: "sitemap.xml", style: "tableCell" },
          {
            text: security.sitemap_xml ? "Найден" : "Не найден",
            style: "tableCellBold",
            alignment: "center",
            color: security.sitemap_xml ? COLORS.good : COLORS.moderate,
          },
        ],
        [
          { text: "Directory listing", style: "tableCell" },
          {
            text: directoryText,
            style: "tableCell",
            alignment: "center",
            color: directoryColor,
          },
        ],
      ],
    },
    layout: plainStripedTableLayout,
  };
};

export const buildSecuritySection = (security: PdfSecurityAudit): any[] => {
  const headerBlock: any = {
    unbreakable: true,
    margin: [0, 12, 0, 0],
    stack: [
      { text: "Безопасность", style: "sectionTitle" },
      {
        text: `Проверено: ${security.checked_url || security.host || "—"}`,
        style: "muted",
        margin: [0, 0, 0, 8],
      },
      { text: "Заголовки безопасности", style: "subsectionTitle" },
      buildHeadersTable(security),
    ],
  };

  const sensitiveBlock: any = {
    unbreakable: true,
    margin: [0, 12, 0, 0],
    stack: [
      { text: "Чувствительные файлы", style: "subsectionTitle" },
      buildSensitiveFilesTable(security),
    ],
  };

  const additionalBlock: any = {
    unbreakable: true,
    margin: [0, 12, 0, 12],
    stack: [
      { text: "Дополнительно", style: "subsectionTitle" },
      buildAdditionalTable(security),
    ],
  };

  return [headerBlock, sensitiveBlock, additionalBlock];
};
