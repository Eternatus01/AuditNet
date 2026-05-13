import { COLORS } from "./constants";

export const stripedTableLayout = {
  fillColor: (rowIndex: number) =>
    rowIndex === 0 ? COLORS.headerBg : rowIndex % 2 === 0 ? COLORS.subtle : null,
  hLineColor: () => COLORS.border,
  vLineColor: () => COLORS.border,
  hLineWidth: () => 0.6,
  vLineWidth: () => 0.6,
  paddingTop: () => 6,
  paddingBottom: () => 6,
};

export const compactStripedTableLayout = {
  ...stripedTableLayout,
  paddingTop: () => 5,
  paddingBottom: () => 5,
};

export const plainStripedTableLayout = {
  fillColor: (rowIndex: number) => (rowIndex % 2 === 0 ? COLORS.subtle : null),
  hLineColor: () => COLORS.border,
  vLineColor: () => COLORS.border,
  hLineWidth: () => 0.6,
  vLineWidth: () => 0.6,
  paddingTop: () => 5,
  paddingBottom: () => 5,
};

export const recommendationCardLayout = {
  fillColor: () => COLORS.subtle,
  hLineColor: () => COLORS.border,
  vLineColor: () => COLORS.border,
  hLineWidth: (i: number, node: { table: { body: unknown[] } }) =>
    i === 0 || i === node.table.body.length ? 0.6 : 0,
  vLineWidth: () => 0.6,
  paddingTop: () => 8,
  paddingBottom: () => 8,
  paddingLeft: () => 10,
  paddingRight: () => 10,
};
