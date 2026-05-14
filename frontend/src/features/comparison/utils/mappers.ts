import type { GuestAuditData, SecurityAudit } from "@/features/dashboard/types";
import type { ComparisonSite } from "@/features/history/types";
import type { ComparisonResult } from "./comparisonHelpers";
import { getHostname } from "./comparisonHelpers";

const mapSecurityAudit = (site: ComparisonSite): SecurityAudit | null => {
  if (!site.security_audit) return null;

  return {
    checked_url: site.url,
    host: getHostname(site.url),
    headers: site.security_audit.headers,
    sensitive_files: site.security_audit.sensitive_files,
    directory_listing: site.security_audit.directory_listing,
    robots_txt: site.security_audit.robots_txt ? "Найден" : null,
    sitemap_xml: site.security_audit.sitemap_xml,
    scripts_info: site.security_audit.scripts_info.map((script) => script.src),
  };
};

export const mapComparisonSiteToAuditData = (site: ComparisonSite): GuestAuditData | null => {
  if (site.error_message) return null;

  return {
    url: site.url,
    performance: site.performance,
    accessibility: site.accessibility,
    best_practices: site.best_practices,
    seo: site.seo,
    lcp: site.lcp,
    fid: site.fid,
    cls: site.cls,
    fcp: site.fcp,
    tbt: site.tbt,
    speed_index: site.speed_index,
    security_audit: mapSecurityAudit(site),
    recommendations: site.recommendations || [],
  };
};

export const mapComparisonSiteToResult = (site: ComparisonSite): ComparisonResult => ({
  url: site.url,
  data: mapComparisonSiteToAuditData(site),
  error: site.error_message,
});
