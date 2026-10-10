/** Public settings only. The static build reads these on every page load. */
declare global {
  interface Window {
    TKPRO_CONFIG?: { apiUrl?: string; siteUrl?: string };
  }
}

export function getPublicApiUrl(): string {
  const configured = typeof window !== "undefined" ? window.TKPRO_CONFIG?.apiUrl : undefined;
  const value = configured || process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api/v1";
  const url = typeof window !== "undefined" ? new URL(value, window.location.origin) : new URL(value);
  if (!/^https?:$/.test(url.protocol)) throw new Error("API URL phải dùng HTTP hoặc HTTPS.");
  return url.href.replace(/\/$/, "");
}

export function getPublicSiteUrl(): string {
  if (typeof window !== "undefined") {
    return new URL(window.TKPRO_CONFIG?.siteUrl || window.location.origin, window.location.origin).origin;
  }
  return process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000";
}
