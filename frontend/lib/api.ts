/**
 * API client tập trung cho toàn bộ frontend TKPRO.
 *
 * - `apiFetch`: dùng cho server components / route handlers. Tự ưu tiên
 *   `API_INTERNAL_URL` (docker) rồi fallback về `NEXT_PUBLIC_API_URL`.
 * - `apiFetchClient`: dùng cho client components, luôn dùng `NEXT_PUBLIC_API_URL`.
 * - Mọi response tuân envelope { success, message, data, meta?, errors? }.
 */

import type { ApiEnvelope } from "@/types";
import { getPublicApiUrl } from "@/lib/runtime-config";

export function getApiBase(): string {
  if (typeof window !== "undefined") return getPublicApiUrl();
  const internal = process.env.API_INTERNAL_URL;
  if (internal && internal.trim().length > 0) return internal;
  return getPublicApiUrl();
}

export class ApiError extends Error {
  status: number;
  errors?: Record<string, string[]>;
  success = false;

  constructor(status: number, message: string, errors?: Record<string, string[]>) {
    super(message);
    this.status = status;
    this.errors = errors;
    // A static page must not silently look empty when the API/CORS is unavailable.
    if (typeof window !== "undefined" && window.TKPRO_CONFIG && (status === 0 || status >= 500 || (status >= 200 && status < 300))) {
      window.dispatchEvent(new CustomEvent("tkpro:api-error", { detail: message }));
    }
  }
}

type FetchOptions = {
  /** Bỏ qua fallback internal -> public, dùng public URL trực tiếp (client) */
  client?: boolean;
  /** Ghi đè base URL */
  base?: string;
  /** ISR revalidate (giây) cho server fetch */
  revalidate?: number;
  /** Tag cache của next */
  tags?: string[];
  /** Timeout ms, mặc định 15000 */
  timeout?: number;
};

function buildUrl(path: string, query?: Record<string, string | number | boolean | undefined>, base?: string): string {
  const root = (base ?? getApiBase()).replace(/\/$/, "");
  const p = path.startsWith("/") ? path : `/${path}`;
  const url = new URL(root + p);
  if (query) {
    for (const [k, v] of Object.entries(query)) {
      if (v === undefined || v === null || v === "") continue;
      url.searchParams.set(k, String(v));
    }
  }
  return url.toString();
}

export async function apiFetch<T>(
  path: string,
  query?: Record<string, string | number | boolean | undefined>,
  options: FetchOptions = {},
): Promise<T> {
  const base = options.client ? getPublicApiUrl() : options.base ?? getApiBase();
  const url = buildUrl(path, query, base);
  const timeout = options.timeout ?? 15000;
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), timeout);

  try {
    const res = await fetch(url, {
      signal: controller.signal,
      headers: { Accept: "application/json" },
      ...(typeof window === "undefined" && options.revalidate !== undefined
        ? { next: { revalidate: options.revalidate, tags: options.tags } } : { cache: "no-store" as const }),
    });

    let json: ApiEnvelope<T> | null = null;
    try {
      json = (await res.json()) as ApiEnvelope<T>;
    } catch {
      // body không phải JSON
    }

    if (!res.ok) {
      const message =
        json && typeof json.message === "string" && json.message.length > 0
          ? json.message
          : res.status === 404
            ? "Không tìm thấy dữ liệu."
            : res.status === 401
              ? "Bạn không có quyền truy cập."
              : res.status === 429
                ? "Bạn gửi quá nhiều yêu cầu. Vui lòng thử lại sau."
                : `Đã xảy ra lỗi (mã ${res.status}).`;
      throw new ApiError(res.status, message, json?.errors);
    }

    if (!json || typeof json.success === "undefined") {
      throw new ApiError(res.status, "Phản hồi từ máy chủ không hợp lệ.");
    }

    if (json.success === false) {
      throw new ApiError(res.status, json.message || "Đã xảy ra lỗi.", json.errors);
    }

    return json.data;
  } catch (err) {
    if (err instanceof ApiError) throw err;
    if (err instanceof DOMException && err.name === "AbortError") {
      throw new ApiError(0, "Máy chủ phản hồi quá lâu. Vui lòng thử lại.");
    }
    throw new ApiError(0, "Không thể kết nối tới máy chủ. Vui lòng kiểm tra kết nối mạng.");
  } finally {
    clearTimeout(timer);
  }
}

export async function apiFetchWithMeta<T>(
  path: string,
  query?: Record<string, string | number | boolean | undefined>,
  options: FetchOptions = {},
): Promise<{ data: T; meta?: ApiEnvelope<T>["meta"] }> {
  const base = options.client ? getPublicApiUrl() : options.base ?? getApiBase();
  const url = buildUrl(path, query, base);
  const timeout = options.timeout ?? 15000;
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), timeout);

  try {
    const res = await fetch(url, {
      signal: controller.signal,
      headers: { Accept: "application/json" },
      ...(typeof window === "undefined" && options.revalidate !== undefined
        ? { next: { revalidate: options.revalidate, tags: options.tags } } : { cache: "no-store" as const }),
    });
    let json: ApiEnvelope<T> | null = null;
    try {
      json = (await res.json()) as ApiEnvelope<T>;
    } catch {
      /* ignore */
    }
    if (!res.ok || !json || json.success !== true) {
      const message =
        (json && json.message) ||
        (res.status === 404 ? "Không tìm thấy dữ liệu." : "Đã xảy ra lỗi. Vui lòng thử lại.");
      throw new ApiError(res.status, message, json?.errors);
    }
    return { data: json.data, meta: json.meta };
  } catch (err) {
    if (err instanceof ApiError) throw err;
    if (err instanceof DOMException && err.name === "AbortError") {
      throw new ApiError(0, "Máy chủ phản hồi quá lâu. Vui lòng thử lại.");
    }
    throw new ApiError(0, "Không thể kết nối tới máy chủ. Vui lòng kiểm tra kết nối mạng.");
  } finally {
    clearTimeout(timer);
  }
}

/** POST JSON, dùng cho form liên hệ phía client. Trả về envelope đầy đủ để đọc errors. */
export async function apiPost<T>(
  path: string,
  body: Record<string, unknown>,
): Promise<ApiEnvelope<T>> {
  const url = buildUrl(path, undefined, getPublicApiUrl());
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), 15000);

  try {
    const res = await fetch(url, {
      method: "POST",
      signal: controller.signal,
      headers: { Accept: "application/json", "Content-Type": "application/json" },
      body: JSON.stringify(body),
    });
    let json: ApiEnvelope<T> | null = null;
    try {
      json = (await res.json()) as ApiEnvelope<T>;
    } catch {
      /* ignore */
    }
    if (!res.ok || !json) {
      const message =
        (json && json.message) ||
        (res.status === 422
          ? "Thông tin gửi chưa hợp lệ. Vui lòng kiểm tra lại."
          : res.status === 429
            ? "Bạn gửi quá nhiều lần. Vui lòng thử lại sau."
            : "Đã xảy ra lỗi. Vui lòng thử lại.");
      throw new ApiError(res.status, message, json?.errors);
    }
    if (json.success === false) {
      throw new ApiError(res.status, json.message || "Đã xảy ra lỗi.", json.errors);
    }
    return json;
  } catch (err) {
    if (err instanceof ApiError) throw err;
    if (err instanceof DOMException && err.name === "AbortError") {
      throw new ApiError(0, "Máy chủ phản hồi quá lâu. Vui lòng thử lại.");
    }
    throw new ApiError(0, "Không thể kết nối tới máy chủ. Vui lòng kiểm tra kết nối mạng.");
  } finally {
    clearTimeout(timer);
  }
}
