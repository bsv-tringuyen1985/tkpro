import { apiFetch } from "@/lib/api";
import type { Menu, MenuItem } from "@/types";

/** GET /api/v1/menus/{location} — header | footer, cây item đã sắp xếp. */
export async function getMenu(location: "header" | "footer"): Promise<Menu> {
  return apiFetch<Menu>(`/menus/${location}`, undefined, { revalidate: 300 });
}

/** Chuẩn hóa URL cho menu item: page -> /{page_slug}, url -> giữ nguyên. */
export function resolveMenuItemUrl(item: MenuItem): string {
  if (item.type === "page" && !item.url) {
    // API nên trả kèm page_slug; nếu thiếu, không đoán URL để tránh link chết.
    if (item.page_slug) return `/${item.page_slug}`;
    return "/";
  }
  const url = (item.url ?? "/").trim();
  return url.length > 0 ? url : "/";
}
