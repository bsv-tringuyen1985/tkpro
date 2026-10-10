export function CardSkeleton() {
  return (
    <div className="overflow-hidden rounded-2xl border border-line bg-white">
      <div className="h-56 animate-pulse bg-linear-to-br from-brand-mint to-line" />
      <div className="space-y-3 p-4">
        <div className="h-4 w-2/3 animate-pulse rounded bg-line" />
        <div className="h-4 w-1/2 animate-pulse rounded bg-line" />
        <div className="h-5 w-1/3 animate-pulse rounded bg-brand-mint" />
        <div className="h-10 animate-pulse rounded-lg bg-cream" />
      </div>
    </div>
  );
}

export function GridSkeleton({ count = 8 }: { count?: number }) {
  return (
    <div className="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 lg:grid-cols-4 lg:gap-5" role="status" aria-label="Đang tải sản phẩm">
      {Array.from({ length: count }).map((_, i) => (
        <CardSkeleton key={i} />
      ))}
    </div>
  );
}

export function HeroSkeleton() {
  return (
    <div className="mx-auto max-w-[1180px] px-6 pt-6">
      <div className="h-[475px] animate-pulse rounded-[30px] bg-linear-to-br from-brand-darker to-brand" />
    </div>
  );
}
