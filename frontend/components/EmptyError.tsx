import Link from "next/link";

export function EmptyState({
  title = "Chưa có dữ liệu",
  description,
  actionHref,
  actionLabel,
}: {
  title?: string;
  description?: string;
  actionHref?: string;
  actionLabel?: string;
}) {
  return (
    <div className="flex flex-col items-center gap-3 rounded-3xl border border-dashed border-line bg-white px-6 py-16 text-center">
      <div className="grid h-16 w-16 place-items-center rounded-full bg-brand-mint text-3xl">📦</div>
      <h3 className="font-serif text-xl font-bold">{title}</h3>
      {description && <p className="max-w-md text-sm leading-relaxed text-muted">{description}</p>}
      {actionHref && actionLabel && (
        <Link
          href={actionHref}
          className="mt-2 inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-dark"
        >
          {actionLabel}
        </Link>
      )}
    </div>
  );
}

export function ErrorState({
  title = "Đã xảy ra lỗi",
  description = "Không tải được dữ liệu. Vui lòng thử lại sau.",
  retryHref,
}: {
  title?: string;
  description?: string;
  retryHref?: string;
}) {
  return (
    <div className="flex flex-col items-center gap-3 rounded-3xl border border-line bg-white px-6 py-16 text-center">
      <div className="grid h-16 w-16 place-items-center rounded-full bg-red-50 text-3xl">⚠️</div>
      <h3 className="font-serif text-xl font-bold">{title}</h3>
      <p className="max-w-md text-sm leading-relaxed text-muted">{description}</p>
      <div className="mt-2 flex flex-wrap justify-center gap-2">
        {retryHref && (
          <Link
            href={retryHref}
            className="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-dark"
          >
            Thử lại
          </Link>
        )}
        <Link
          href="/"
          className="inline-flex items-center gap-2 rounded-full border border-line px-5 py-2.5 text-sm font-bold text-ink hover:border-brand"
        >
          Về trang chủ
        </Link>
      </div>
    </div>
  );
}
