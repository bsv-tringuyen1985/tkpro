import { apiFetch, apiFetchWithMeta } from "@/lib/api";
import type { Product, ProductCategory, ApiMeta } from "@/types";

export type ProductListParams = {
  q?: string;
  category?: string; // slug danh mục
  featured?: 1 | 0;
  per_page?: number;
  page?: number;
  sort?: string;
};

export type ProductListResult = { items: Product[]; meta: ApiMeta };

/** GET /api/v1/products — phân trang khi có per_page/page, ngược lại trả mảng. */
export async function getProducts(params: ProductListParams = {}): Promise<ProductListResult> {
  const useMeta = params.per_page !== undefined || params.page !== undefined;
  if (useMeta) {
    const { data, meta } = await apiFetchWithMeta<Product[]>(`/products`, params, {
      revalidate: 120,
    });
    return { items: data, meta: meta ?? {} };
  }
  const data = await apiFetch<Product[]>(`/products`, params, { revalidate: 120 });
  return { items: data, meta: {} };
}

/** GET /api/v1/products/{slug} — kèm category, images, related. */
export async function getProduct(slug: string): Promise<Product> {
  const data = await apiFetch<{ product: Product; related: Product[] }>(`/products/${encodeURIComponent(slug)}`, undefined, {
    revalidate: 120,
  });
  return { ...data.product, related: data.related };
}

/** GET /api/v1/categories — cây danh mục + đếm sản phẩm. */
export async function getCategories(): Promise<ProductCategory[]> {
  return apiFetch<ProductCategory[]>(`/categories`, undefined, { revalidate: 300 });
}

/** GET /api/v1/categories/{slug}. */
export async function getCategory(slug: string): Promise<ProductCategory> {
  const data = await apiFetch<{ category: ProductCategory; products: Product[] }>(`/categories/${encodeURIComponent(slug)}`, undefined, {
    revalidate: 300,
  });
  return data.category;
}
