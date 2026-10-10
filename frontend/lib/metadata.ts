import type { Metadata } from "next";
import { getSettings } from "@/services/settingService";
import type { SeoMeta } from "@/types";
import { absoluteImageUrl } from "@/utils/format";

type SeoSource = {
  seo?: SeoMeta | null;
  meta_title?: string | null;
  meta_description?: string | null;
  meta_keywords?: string | null;
  title?: string;
  name?: string;
  excerpt?: string | null;
  short_description?: string | null;
  description?: string | null;
  featured_image?: string | null;
  thumbnail?: string | null;
};

const FALLBACK_DESCRIPTION =
  "TKPRO — thiết bị điện tử, camera an ninh và linh kiện máy tính chính hãng. Tư vấn đúng nhu cầu, lắp đặt chuẩn, bảo hành rõ ràng.";

/** Dựng Metadata cho trang từ trường SEO của API + settings. */
export async function buildMetadata(source: SeoSource, opts?: { canonical?: string }): Promise<Metadata> {
  const s = await getSettings().catch(() => null);
  const siteName = s?.site_name ?? "TKPRO";
  const seo = source.seo;
  const title = seo?.meta_title || source.meta_title || source.title || source.name || siteName;
  const description =
    seo?.meta_description || source.meta_description || source.excerpt || source.short_description || FALLBACK_DESCRIPTION;
  const keywordText = seo?.meta_keywords || source.meta_keywords;
  const keywords = keywordText
    ? keywordText.split(",").map((k) => k.trim()).filter(Boolean)
    : undefined;
  const image = absoluteImageUrl(seo?.og_image || source.featured_image || source.thumbnail) || undefined;

  const meta: Metadata = {
    title,
    description,
    ...(keywords ? { keywords } : {}),
    openGraph: {
      title: seo?.og_title || title,
      description: seo?.og_description || description,
      type: "website",
      siteName,
      locale: "vi_VN",
      ...(image ? { images: [{ url: image }] } : {}),
    },
    twitter: {
      card: "summary_large_image",
      title,
      description,
      ...(image ? { images: [image] } : {}),
    },
  };
  const canonical = seo?.canonical_url || opts?.canonical;
  if (canonical) meta.alternates = { canonical };
  if (seo?.robots) meta.robots = seo.robots;
  return meta;
}
