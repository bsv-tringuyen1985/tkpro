/** Tiện ích định dạng, slug, chuỗi dùng chung. */
import { getPublicApiUrl } from "@/lib/runtime-config";

/** "890.000₫" — dùng toLocaleString('vi-VN'). */
export function formatPriceVnd(value: number): string {
  return `${value.toLocaleString("vi-VN")}₫`;
}

/**
 * Nhãn giá của sản phẩm:
 * - có price       -> "890.000₫"
 * - có price_label -> price_label (vd "Giá tham khảo: 890.000₫")
 * - null           -> "Liên hệ"
 */
export function priceLabel(price: number | null, label?: string | null): string {
  if (price !== null && price !== undefined) {
    if (label && label.trim().length > 0) return label;
    return formatPriceVnd(price);
  }
  return "Liên hệ";
}

/** "Giá tham khảo: 890.000₫" hoặc "Giá: Liên hệ" (dùng ở trang chi tiết). */
export function fullPriceLabel(price: number | null, label?: string | null): string {
  if (price !== null && price !== undefined) {
    if (label && label.trim().length > 0) return label;
    return `Giá tham khảo: ${formatPriceVnd(price)}`;
  }
  return "Giá: Liên hệ";
}

/** Xóa dấu tiếng Việt, lowercase, gạch ngang -> slug. */
export function toSlug(text: string): string {
  return text
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/đ/g, "d")
    .replace(/Đ/g, "d")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .replace(/-{2,}/g, "-");
}

/** Chuyển URL ảnh tương đối (/storage/...) thành URL tuyệt đối phía browser. */
export function absoluteImageUrl(path?: string | null): string {
  if (!path) return "";
  if (path.startsWith("http://") || path.startsWith("https://")) return path;
  const base = getPublicApiUrl();
  // NEXT_PUBLIC_API_URL kết thúc bằng /api/v1 -> gốc là phần trước đó
  const origin = base.replace(/\/api\/v1\/?$/, "");
  const p = path.startsWith("/") ? path : `/${path}`;
  return origin + p;
}

/** Rút gọn chuỗi ở ranh giới từ. */
export function truncate(text: string | null | undefined, max = 140): string {
  if (!text) return "";
  const t = text.trim();
  if (t.length <= max) return t;
  const cut = t.slice(0, max);
  const lastSpace = cut.lastIndexOf(" ");
  return (lastSpace > 20 ? cut.slice(0, lastSpace) : cut) + "…";
}

/** Xóa thẻ HTML (dùng cho excerpt từ content). */
export function stripHtml(html: string | null | undefined): string {
  if (!html) return "";
  return html.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
}

/** Định dạng ngày kiểu vi-VN: "07/10/2026". */
export function formatDateVi(value: string | null | undefined): string {
  if (!value) return "";
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return "";
  return d.toLocaleDateString("vi-VN", { day: "2-digit", month: "2-digit", year: "numeric" });
}

/** Chuỗi truy vấn cho pagination links. */
export function buildQuery(params: Record<string, string | number | undefined>): string {
  const sp = new URLSearchParams();
  for (const [k, v] of Object.entries(params)) {
    if (v === undefined || v === "") continue;
    sp.set(k, String(v));
  }
  const s = sp.toString();
  return s ? `?${s}` : "";
}
