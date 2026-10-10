import Link from "next/link";
import { buildQuery } from "@/utils/format";

export default function Pagination({
  basePath,
  currentPage,
  lastPage,
  total,
  extra,
}: {
  basePath: string;
  currentPage: number;
  lastPage: number;
  total?: number;
  extra?: Record<string, string | number | undefined>;
}) {
  if (lastPage <= 1) return null;

  const href = (page: number) => basePath + buildQuery({ ...(extra ?? {}), page });

  const pages: number[] = [];
  const from = Math.max(1, currentPage - 2);
  const to = Math.min(lastPage, currentPage + 2);
  for (let p = from; p <= to; p++) pages.push(p);

  return (
    <nav aria-label="Phân trang" className="mt-10 flex flex-col items-center gap-3">
      <div className="flex flex-wrap items-center justify-center gap-2">
        {currentPage > 1 && (
          <Link
            href={href(currentPage - 1)}
            className="rounded-full border border-line bg-white px-4 py-2 text-sm font-bold text-brand hover:border-brand"
          >
            ← Trước
          </Link>
        )}
        {from > 1 && (
          <>
            <Link
              href={href(1)}
              className="rounded-full border border-line bg-white px-4 py-2 text-sm font-bold text-ink hover:border-brand"
            >
              1
            </Link>
            {from > 2 && <span className="text-muted">…</span>}
          </>
        )}
        {pages.map((p) => (
          <Link
            key={p}
            href={href(p)}
            aria-current={p === currentPage ? "page" : undefined}
            className={`rounded-full px-4 py-2 text-sm font-bold ${
              p === currentPage
                ? "bg-brand text-white"
                : "border border-line bg-white text-ink hover:border-brand"
            }`}
          >
            {p}
          </Link>
        ))}
        {to < lastPage && (
          <>
            {to < lastPage - 1 && <span className="text-muted">…</span>}
            <Link
              href={href(lastPage)}
              className="rounded-full border border-line bg-white px-4 py-2 text-sm font-bold text-ink hover:border-brand"
            >
              {lastPage}
            </Link>
          </>
        )}
        {currentPage < lastPage && (
          <Link
            href={href(currentPage + 1)}
            className="rounded-full border border-line bg-white px-4 py-2 text-sm font-bold text-brand hover:border-brand"
          >
            Tiếp →
          </Link>
        )}
      </div>
      {typeof total === "number" && (
        <p className="text-xs text-muted">
          Trang {currentPage}/{lastPage} · Tổng {total} mục
        </p>
      )}
    </nav>
  );
}
