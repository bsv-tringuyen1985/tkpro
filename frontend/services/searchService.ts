import { apiFetch } from "@/lib/api";
import type { SearchResult } from "@/types";

/** GET /api/v1/search?q= — gộp products + posts. */
export async function searchAll(q: string): Promise<SearchResult> {
  if (!q.trim()) return { products: [], posts: [] };
  const data = await apiFetch<SearchResult>(`/search`, { q: q.trim() }, { client: true });
  return {
    products: Array.isArray(data.products) ? data.products : [],
    posts: Array.isArray(data.posts) ? data.posts : [],
  };
}
