import type { MetadataRoute } from "next";
import type { ApiMeta } from "@/types";
import { getProducts, getCategories } from "@/services/productService";
import { getPosts } from "@/services/postService";

const SITE_URL = (process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000").replace(/\/$/, "");

/** Sitemap động: trang tĩnh + danh mục + sản phẩm + bài viết từ API. */
export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const now = new Date();
  const staticRoutes: MetadataRoute.Sitemap = [
    { url: `${SITE_URL}/`, lastModified: now, changeFrequency: "daily", priority: 1 },
    { url: `${SITE_URL}/gioi-thieu`, lastModified: now, changeFrequency: "monthly", priority: 0.8 },
    { url: `${SITE_URL}/san-pham`, lastModified: now, changeFrequency: "daily", priority: 0.9 },
    { url: `${SITE_URL}/dich-vu`, lastModified: now, changeFrequency: "monthly", priority: 0.8 },
    { url: `${SITE_URL}/tin-tuc`, lastModified: now, changeFrequency: "daily", priority: 0.8 },
    { url: `${SITE_URL}/lien-he`, lastModified: now, changeFrequency: "yearly", priority: 0.7 },
  ];

  try {
    const [categories, productsRes, postsRes] = await Promise.all([
      getCategories().catch(() => []),
      getProducts({ per_page: 500 }).catch(() => ({ items: [], meta: ({} as ApiMeta) })),
      getPosts({ per_page: 500 }).catch(() => ({ items: [], meta: ({} as ApiMeta) })),
    ]);

    const catUrls: MetadataRoute.Sitemap = categories.map((c) => ({
      url: `${SITE_URL}/san-pham/${c.slug}`,
      lastModified: now,
      changeFrequency: "weekly",
      priority: 0.8,
    }));

    const productUrls: MetadataRoute.Sitemap = productsRes.items.map((p) => ({
      url: `${SITE_URL}/san-pham/${p.category?.slug ?? "san-pham"}/${p.slug}`,
      lastModified: p.created_at ? new Date(p.created_at) : now,
      changeFrequency: "weekly",
      priority: 0.9,
    }));

    const postUrls: MetadataRoute.Sitemap = postsRes.items.map((p) => ({
      url: `${SITE_URL}/tin-tuc/${p.slug}`,
      lastModified: p.published_at ? new Date(p.published_at) : now,
      changeFrequency: "weekly",
      priority: 0.7,
    }));

    return [...staticRoutes, ...catUrls, ...productUrls, ...postUrls];
  } catch {
    // API chưa sẵn sàng (build time): trả về sitemap tĩnh tối thiểu
    return staticRoutes;
  }
}
