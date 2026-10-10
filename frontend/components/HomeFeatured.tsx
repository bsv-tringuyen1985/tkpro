"use client";

import { useMemo, useState } from "react";
import Link from "next/link";
import ProductCard from "./ProductCard";
import type { Product } from "@/types";

const CHIP_DEFS = [
  { key: "all", label: "Tất cả", slug: null as string | null },
  { key: "camera", label: "Camera", slug: "camera-giam-sat" },
  { key: "pc", label: "Linh kiện PC", slug: "linh-kien-pc" },
  { key: "device", label: "Điện tử", slug: "thiet-bi-dien-tu" },
];

/** Section "Sản phẩm nổi bật" + filter chips, lọc client-side theo danh mục. */
export default function HomeFeatured({ products }: { products: Product[] }) {
  const [active, setActive] = useState("all");

  const filtered = useMemo(() => {
    const chip = CHIP_DEFS.find((c) => c.key === active);
    if (!chip || !chip.slug) return products;
    return products.filter((p) => p.category?.slug === chip.slug);
  }, [active, products]);

  return (
    <div>
      <div className="mb-6 flex flex-wrap gap-2" role="tablist" aria-label="Lọc sản phẩm nổi bật">
        {CHIP_DEFS.map((c) => (
          <button
            key={c.key}
            type="button"
            role="tab"
            aria-selected={active === c.key}
            onClick={() => setActive(c.key)}
            className={`rounded-full border px-4 py-2.5 text-xs font-bold transition ${
              active === c.key
                ? "border-brand bg-brand text-white"
                : "border-line bg-white text-ink hover:border-brand"
            }`}
          >
            {c.label}
          </button>
        ))}
      </div>

      {filtered.length === 0 ? (
        <p className="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center text-sm text-muted">
          Chưa có sản phẩm nào trong nhóm này.
        </p>
      ) : (
        <div className="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 lg:grid-cols-4 lg:gap-5">
          {filtered.map((p, i) => (
            <ProductCard key={p.id} product={p} index={i} />
          ))}
        </div>
      )}

      <div className="mt-8 text-center">
        <Link
          href="/san-pham"
          className="inline-flex items-center gap-2 rounded-full border border-brand px-6 py-3 text-sm font-bold text-brand hover:bg-brand hover:text-white"
        >
          Xem tất cả sản phẩm →
        </Link>
      </div>
    </div>
  );
}
