import { COLORS } from "./constants";

export const defaultPdfStyles = {
  brand: { fontSize: 11, color: "#ffffff", bold: true, characterSpacing: 2 },
  heroTitle: { fontSize: 22, bold: true, color: "#ffffff" },
  heroUrl: { fontSize: 12, color: "#ffffff" },
  heroDate: { fontSize: 10, color: "#e0e7ff" },
  section: { margin: [0, 12, 0, 12] },
  sectionTitle: {
    fontSize: 16,
    bold: true,
    color: COLORS.primaryDark,
    margin: [0, 0, 0, 10],
  },
  subsectionTitle: {
    fontSize: 12,
    bold: true,
    color: COLORS.text,
    margin: [0, 6, 0, 6],
  },
  tableHeader: {
    bold: true,
    fontSize: 10,
    color: COLORS.text,
    fillColor: COLORS.headerBg,
  },
  tableCell: { fontSize: 10, color: COLORS.text },
  tableCellBold: { fontSize: 10, bold: true, color: COLORS.text },
  tableCellMuted: { fontSize: 9, color: COLORS.muted },
  muted: { fontSize: 9, color: COLORS.muted, italics: true },
  recTitle: { fontSize: 11, bold: true, color: COLORS.primaryDark },
  recValue: { fontSize: 10, color: COLORS.moderate, bold: true, margin: [0, 4, 0, 0] },
  recSolution: { fontSize: 10, color: COLORS.text },
  runningHeader: { fontSize: 9, color: COLORS.muted },
  footer: { fontSize: 8, color: COLORS.muted },
};

export const defaultPdfDefaultStyle = {
  font: "Roboto",
  fontSize: 10,
  color: COLORS.text,
  lineHeight: 1.3,
};
