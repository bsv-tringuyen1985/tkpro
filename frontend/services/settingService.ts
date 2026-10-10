import { apiFetch } from "@/lib/api";
import type { Settings } from "@/types";

/** GET /api/v1/settings — chỉ group public (general, contact, social, seo). */
export async function getSettings(): Promise<Settings> {
  try {
    const data = await apiFetch<unknown>(`/settings`, undefined, { revalidate: 300 });
    return normalizeSettings(toKeyValue(data));
  } catch {
    return normalizeSettings({});
  }
}

/** Chấp nhận cả dạng {key: value} lẫn [{key, value}, ...] phòng khi API trả khác contract. */
function toKeyValue(data: unknown): Record<string, string | null | undefined> {
  if (Array.isArray(data)) {
    const out: Record<string, string | null | undefined> = {};
    for (const row of data) {
      if (row && typeof row === "object" && "key" in row) {
        const k = String((row as { key: unknown }).key);
        out[k] = (row as { value?: string | null }).value ?? null;
      }
    }
    return out;
  }
  if (data && typeof data === "object") {
    return data as Record<string, string | null | undefined>;
  }
  return {};
}

export const DEFAULT_SETTINGS: Settings = {
  site_name: "TKPRO",
  hotline: "1900 6868",
  email: "support@techvision.vn",
  address: "123 Nguyễn Trãi, TP.HCM",
  working_hours: "08:00 — 21:00 mỗi ngày",
  announcement: "Hàng chính hãng · Bảo hành tận tâm · Giao hàng toàn quốc",
  facebook: "#",
  youtube: "#",
  zalo: "#",
  google_maps_embed: "",
  copyright: "© 2026 TKPRO · Điện tử · Camera · Linh kiện máy tính · Bảo hành chính hãng",
  footer_about: "Thiết bị điện tử, camera và linh kiện máy tính chính hãng.",
  hero_eyebrow: "TKPRO · Công nghệ chính hãng",
  hero_title: "Nâng cấp không gian, làm chủ công nghệ.",
  hero_sub:
    "Khám phá hệ sinh thái thiết bị điện tử, camera an ninh và linh kiện máy tính chính hãng. Tư vấn đúng nhu cầu, lắp đặt chuẩn và bảo hành rõ ràng.",
  trust_1_num: "8+",
  trust_1_label: "Danh mục thiết bị",
  trust_2_num: "100%",
  trust_2_label: "Hàng chính hãng",
  trust_3_num: "24/7",
  trust_3_label: "Hỗ trợ kỹ thuật",
  hours: "08:00 — 21:00 mỗi ngày",
};

export function normalizeSettings(raw: Record<string, string | null | undefined>): Settings {
  const out = { ...DEFAULT_SETTINGS } as Settings;
  for (const [k, v] of Object.entries(raw)) {
    if (v !== null && v !== undefined) out[k] = v;
  }
  return out;
}
