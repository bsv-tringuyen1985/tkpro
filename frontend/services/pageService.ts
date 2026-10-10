import { apiFetch } from "@/lib/api";
import type { Page } from "@/types";

/** GET /api/v1/pages/{slug}. */
export async function getPage(slug: string): Promise<Page> {
  return apiFetch<Page>(`/pages/${encodeURIComponent(slug)}`, undefined, {
    revalidate: 300,
  });
}
