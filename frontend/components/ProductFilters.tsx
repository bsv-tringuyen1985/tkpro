"use client";

import { useRouter } from "next/navigation";
import { useState, type FormEvent } from "react";
import type { ProductCategory } from "@/types";

/** Bộ lọc trang danh sách sản phẩm: tìm kiếm + chọn danh mục + sắp xếp. */
export default function ProductFilters({
  categories,
  current,
}: {
  categories: ProductCategory[];
  current: { q?: string; category?: string; sort?: string };
}) {
  const router = useRouter();
  const [q, setQ] = useState(current.q ?? "");

  const go = (next: { q?: string; category?: string; sort?: string }) => {
    const sp = new URLSearchParams();
    if (next.q) sp.set("q", next.q);
    if (next.category) sp.set("category", next.category);
    if (next.sort) sp.set("sort", next.sort);
    router.push(`/san-pham${sp.toString() ? `?${sp.toString()}` : ""}`);
  };

  const submit = (e: FormEvent) => {
    e.preventDefault();
    go({ ...current, q: q.trim() || undefined });
  };

  return (
    <div className="mb-8 grid gap-3 rounded-2xl border border-line bg-white p-4 md:grid-cols-[1fr_auto_auto] md:items-center">
      <form onSubmit={submit} role="search" className="flex gap-2">
        <input
          type="search"
          value={q}
          onChange={(e) => setQ(e.target.value)}
          placeholder="Tìm sản phẩm…"
          aria-label="Tìm sản phẩm"
          className="w-full rounded-full border border-line bg-cream px-4 py-2.5 text-sm outline-hidden focus:border-brand"
        />
        <button
          type="submit"
          className="shrink-0 rounded-full bg-brand px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-dark"
        >
          Tìm
        </button>
      </form>

      <label className="flex items-center gap-2 text-sm">
        <span className="text-muted">Danh mục</span>
        <select
          value={current.category ?? ""}
          onChange={(e) => go({ ...current, category: e.target.value || undefined })}
          className="rounded-full border border-line bg-cream px-4 py-2.5 text-sm font-semibold outline-hidden focus:border-brand"
          aria-label="Lọc theo danh mục"
        >
          <option value="">Tất cả</option>
          {categories.map((c) => (
            <option key={c.id} value={c.slug}>
              {c.name}
            </option>
          ))}
        </select>
      </label>

      <label className="flex items-center gap-2 text-sm">
        <span className="text-muted">Sắp xếp</span>
        <select
          value={current.sort ?? ""}
          onChange={(e) => go({ ...current, sort: e.target.value || undefined })}
          className="rounded-full border border-line bg-cream px-4 py-2.5 text-sm font-semibold outline-hidden focus:border-brand"
          aria-label="Sắp xếp sản phẩm"
        >
          <option value="">Mới nhất</option>
          <option value="price_asc">Giá tăng dần</option>
          <option value="price_desc">Giá giảm dần</option>
          <option value="name_asc">Tên A–Z</option>
        </select>
      </label>
    </div>
  );
}
