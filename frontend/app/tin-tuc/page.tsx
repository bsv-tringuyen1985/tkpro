import type { ApiMeta } from "@/types";
import type { Metadata } from "next";
import Breadcrumb from "@/components/Breadcrumb";
import NewsCard from "@/components/NewsCard";
import Pagination from "@/components/Pagination";
import { EmptyState } from "@/components/EmptyError";
import { getPosts, getPostCategories } from "@/services/postService";
import Link from "next/link";

export const revalidate = 180;

export const metadata: Metadata = { title: "Tin tức" };

type SP = { [key: string]: string | string[] | undefined };

export default async function TinTucPage({ searchParams }: { searchParams: Promise<SP> }) {
  const query = await searchParams;
  const category = typeof query.category === "string" ? query.category : undefined;
  const page = Math.max(1, parseInt(typeof query.page === "string" ? query.page : "1", 10) || 1);

  const [res, cats] = await Promise.all([
    getPosts({ category, page, per_page: 9 }).catch(() => ({ items: [], meta: ({} as ApiMeta) })),
    getPostCategories().catch(() => []),
  ]);

  const currentPage = res.meta.current_page ?? page;
  const lastPage = res.meta.last_page ?? 1;

  return (
    <div className="mx-auto max-w-[1180px] px-4 py-10 md:px-6">
      <Breadcrumb items={[{ label: "Tin tức" }]} />
      <header className="mb-8">
        <span className="text-[11px] font-extrabold uppercase tracking-[2px] text-brand-light">
          Tech insights
        </span>
        <h1 className="mt-2 font-serif text-4xl font-bold tracking-tight md:text-5xl">
          Tin công nghệ & khuyến mãi
        </h1>
      </header>

      {cats.length > 0 && (
        <div className="mb-8 flex flex-wrap gap-2" aria-label="Lọc theo chuyên mục">
          <Link
            href="/tin-tuc"
            className={`rounded-full border px-4 py-2.5 text-xs font-bold ${
              !category ? "border-brand bg-brand text-white" : "border-line bg-white hover:border-brand"
            }`}
          >
            Tất cả
          </Link>
          {cats.map((c) => (
            <Link
              key={c.id}
              href={`/tin-tuc?category=${c.slug}`}
              className={`rounded-full border px-4 py-2.5 text-xs font-bold ${
                category === c.slug
                  ? "border-brand bg-brand text-white"
                  : "border-line bg-white hover:border-brand"
              }`}
            >
              {c.name}
            </Link>
          ))}
        </div>
      )}

      {res.items.length > 0 ? (
        <>
          <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            {res.items.map((p, i) => (
              <NewsCard key={p.id} post={p} index={i} large={currentPage === 1 && i === 0 && !category} />
            ))}
          </div>
          <Pagination
            basePath="/tin-tuc"
            currentPage={currentPage}
            lastPage={lastPage}
            total={res.meta.total}
            extra={{ category }}
          />
        </>
      ) : (
        <EmptyState
          title="Chưa có bài viết"
          description="Tin tức đang được cập nhật. Vui lòng quay lại sau."
          actionHref="/tin-tuc"
          actionLabel="Xem tất cả tin tức"
        />
      )}
    </div>
  );
}
