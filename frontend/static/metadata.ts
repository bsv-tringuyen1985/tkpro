import type { Metadata } from "next";
import { getPublicSiteUrl } from "@/lib/runtime-config";

function titleText(value: Metadata["title"], fallback: string): string {
  if (typeof value === "string") return value;
  if (value && "absolute" in value) return value.absolute;
  if (value && "default" in value) return value.default;
  return fallback;
}

function setMeta(key: string, content: string, attribute = "name") {
  let element = document.head.querySelector<HTMLMetaElement>(`meta[${attribute}="${key}"]`);
  if (!content) { element?.remove(); return; }
  if (!element) {
    element = document.createElement("meta");
    element.setAttribute(attribute, key);
    document.head.append(element);
  }
  element.content = content;
}

export function applyMetadata(metadata: Metadata, siteName = "TKPRO") {
  const title = titleText(metadata.title, "Điện tử · Camera · Linh kiện máy tính");
  const description = metadata.description || "TKPRO — thiết bị điện tử, camera an ninh và linh kiện máy tính chính hãng.";
  document.title = `${title} | ${siteName}`;
  setMeta("description", description);
  setMeta("keywords", Array.isArray(metadata.keywords) ? metadata.keywords.join(", ") : metadata.keywords || "");
  setMeta("robots", typeof metadata.robots === "string" ? metadata.robots : "index,follow");
  const canonical = metadata.alternates?.canonical;
  const canonicalPath = typeof canonical === "string" || canonical instanceof URL ? String(canonical) : window.location.pathname;
  const url = new URL(canonicalPath, getPublicSiteUrl()).href;
  let link = document.head.querySelector<HTMLLinkElement>('link[rel="canonical"]');
  if (!link) { link = document.createElement("link"); link.rel = "canonical"; document.head.append(link); }
  link.href = url;
  setMeta("og:url", url, "property");
  setMeta("og:title", titleText(metadata.openGraph?.title, title), "property");
  setMeta("og:description", metadata.openGraph?.description || description, "property");
  setMeta("og:site_name", siteName, "property");
  setMeta("og:type", "website", "property");
  setMeta("og:locale", "vi_VN", "property");
  const images = metadata.openGraph?.images;
  const first = Array.isArray(images) ? images[0] : images;
  const image = first && typeof first === "object" && "url" in first ? String(first.url) : first ? String(first) : "";
  setMeta("og:image", image, "property");
  setMeta("twitter:card", image ? "summary_large_image" : "summary");
  setMeta("twitter:title", title);
  setMeta("twitter:description", description);
  setMeta("twitter:image", image);
}
