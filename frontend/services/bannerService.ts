import { apiFetch } from "@/lib/api";
import type { Banner } from "@/types";

/** GET /api/v1/banners — chỉ banner đang active, đã sắp xếp. */
export async function getBanners(): Promise<Banner[]> {
  return apiFetch<Banner[]>(`/banners`, undefined, { revalidate: 300 });
}
