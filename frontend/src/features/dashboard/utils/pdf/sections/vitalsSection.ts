/* eslint-disable @typescript-eslint/no-explicit-any */
import {
  formatCls,
  formatMs,
  formatSeconds,
  getClsStatus,
  getFcpStatus,
  getFidStatus,
  getLcpStatus,
  getSpeedIndexStatus,
  getTbtStatus,
} from "../helpers";
import { stripedTableLayout } from "../tableLayouts";
import type { PdfVitals } from "../types";

export const buildVitalsSection = (vitals: PdfVitals): any => {
  const rows = [
    {
      metric: "LCP",
      fullName: "Largest Contentful Paint",
      value: formatSeconds(vitals.lcp),
      target: "< 2.5 с",
      status: getLcpStatus(vitals.lcp),
    },
    {
      metric: "INP",
      fullName: "Interaction to Next Paint",
      value: formatMs(vitals.fid),
      target: "< 200 мс",
      status: getFidStatus(vitals.fid),
    },
    {
      metric: "CLS",
      fullName: "Cumulative Layout Shift",
      value: formatCls(vitals.cls),
      target: "< 0.1",
      status: getClsStatus(vitals.cls),
    },
    {
      metric: "FCP",
      fullName: "First Contentful Paint",
      value: formatSeconds(vitals.fcp),
      target: "< 1.8 с",
      status: getFcpStatus(vitals.fcp),
    },
    {
      metric: "TBT",
      fullName: "Total Blocking Time",
      value: formatSeconds(vitals.tbt),
      target: "< 0.2 с",
      status: getTbtStatus(vitals.tbt),
    },
    {
      metric: "SI",
      fullName: "Speed Index",
      value: formatSeconds(vitals.speedIndex),
      target: "< 3.4 с",
      status: getSpeedIndexStatus(vitals.speedIndex),
    },
  ];

  return {
    style: "section",
    unbreakable: true,
    stack: [
      { text: "Core Web Vitals и метрики производительности", style: "sectionTitle" },
      {
        table: {
          headerRows: 1,
          widths: [50, "*", 80, 70, 120],
          body: [
            [
              { text: "Код", style: "tableHeader" },
              { text: "Метрика", style: "tableHeader" },
              { text: "Значение", style: "tableHeader", alignment: "center" },
              { text: "Цель", style: "tableHeader", alignment: "center" },
              { text: "Статус", style: "tableHeader", alignment: "center" },
            ],
            ...rows.map((r) => [
              { text: r.metric, style: "tableCellBold", alignment: "center" },
              { text: r.fullName, style: "tableCell" },
              { text: r.value, style: "tableCellBold", alignment: "center" },
              { text: r.target, style: "tableCellMuted", alignment: "center" },
              {
                text: r.status.label,
                style: "tableCell",
                alignment: "center",
                color: r.status.color,
              },
            ]),
          ],
        },
        layout: stripedTableLayout,
      },
    ],
  };
};
