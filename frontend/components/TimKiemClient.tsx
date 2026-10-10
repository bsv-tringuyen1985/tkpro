"use client";

import { useMemo, useState } from "react";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import SearchBox from "@/components/SearchBox";
import ProductCard from "@/components/ProductCard";
import NewsCard from "@/components/NewsCard";
import { GridSkeleton } from "@/components/Loading";
import { EmptyState } from "@/components/EmptyError";
import { useSearch } from "@/hooks/useSearch";

/** Client của trang /tim-kiem: kết quả từ hook useSearch, lọc theo danh mục. */
export default function TimKiemClient() {
  const searchParams = useSearchParams();
  const q = searchParams.get("q")?.trim() ?? "";
  const state = useSearch(q);
  const [catFilter, setCatFilter] = useState("all");

  const categories = useMemo(() => {
    if (state.status !== "done") return [];
    const map = new Map<string, string>();
    for (const p of state.result.products) {
      if (p.category?.slug) map.set(p.category.slug, p.category.name);
    }
    return Array.from(map.entries());
  }, [state]);

  const products = useMemo(() => {
    if (state.status !== "done") return [];
    const list = state.result.products;
    return catFilter === "all" ? list : list.filter((p) => p.category?.slug === catFilter);
  }, [state, catFilter]);

  const posts = state.status === "done" ? state.result.posts : [];
  const total = products.length + (catFilter === "all" ? posts.length : 0);

  return (
    <div className="mx-auto max-w-[1180px] px-4 py-10 md:px-6">
      <h1 className="font-serif text-3xl font-bold tracking-tight md:text-4xl">Tìm kiếm</h1>
      <div className="mt-6 max-w-xl">
        <SearchBox initialQuery={q} />
      </div>

      {state.status === "idle" && (
        <div className="mt-10">
          <EmptyState
            title="Nhập từ khóa để tìm kiếm"
            description="Tìm camera, linh kiện PC, thiết bị điện tử và các bài viết hướng dẫn, khuyến mãi."
          />
        </div>
      )}

      {state.status === "loading" && (
        <div className="mt-10">
          <p className="mb-6 text-sm text-muted" role="status">
            Đang tìm “{q}”…
          </p>
          <GridSkeleton count={8} />
        </div>
      )}

      {state.status === "error" && (
        <div className="mt-10">
          <EmptyState title="Tìm kiếm thất bại" description={state.message} />
        </div>
      )}

      {state.status === "done" && (
        <div className="mt-10">
          <p className="text-sm text-muted" role="status">
            Tìm thấy <b className="text-ink">{total}</b> kết quả cho “<b className="text-ink">{q}</b>”
          </p>

          {categories.length > 1 && (
            <div className="mt-4 flex flex-wrap gap-2" aria-label="Lọc theo danh mục">
              {[["all", "Tất cả"], ...categories].map(([slug, name]) => (
                <button
                  key={slug}
                  type="button"
                  onClick={() => setCatFilter(slug)}
                  aria-pressed={catFilter === slug}
                  className={`rounded-full border px-4 py-2 text-xs font-bold ${
                    catFilter === slug
                      ? "border-brand bg-brand text-white"
                      : "border-line bg-white hover:border-brand"
                  }`}
                >
                  {name}
                </button>
              ))}
            </div>
          )}

          {total === 0 ? (
            <div className="mt-6">
              <EmptyState
                title="Không tìm thấy kết quả"
                description={`Không có sản phẩm hay bài viết nào khớp với “${q}”. Thử từ khóa khác như “camera”, “SSD”, “khuyến mãi”.`}
                actionHref="/san-pham"
                actionLabel="Xem tất cả sản phẩm"
              />
            </div>
          ) : (
            <>
              {products.length > 0 && (
                <section className="mt-8" aria-label="Sản phẩm tìm được">
                  <h2 className="mb-4 font-serif text-xl font-bold">Sản phẩm ({products.length})</h2>
                  <div className="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 lg:grid-cols-4 lg:gap-5">
                    {products.map((p, i) => (
                      <ProductCard key={p.id} product={p} index={i} />
                    ))}
                  </div>
                </section>
              )}

              {catFilter === "all" && posts.length > 0 && (
                <section className="mt-10" aria-label="Bài viết tìm được">
                  <h2 className="mb-4 font-serif text-xl font-bold">Bài viết ({posts.length})</h2>
                  <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    {posts.map((p, i) => (
                      <NewsCard key={p.id} post={p} index={i} />
                    ))}
                  </div>
                </section>
              )}
            </>
          )}

          <div className="mt-10 text-center">
            <Link href="/lien-he" className="text-sm font-bold text-brand hover:underline">
              Không tìm thấy món bạn cần? Liên hệ để được tư vấn →
            </Link>
          </div>
        </div>
      )}
    </div>
  );
}
