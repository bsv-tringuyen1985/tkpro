import { GridSkeleton, HeroSkeleton } from "@/components/Loading";

export default function Loading() {
  return (
    <div>
      <HeroSkeleton />
      <div className="mx-auto max-w-[1180px] px-4 py-16 md:px-6">
        <div className="mb-6 h-9 w-64 animate-pulse rounded bg-line" />
        <GridSkeleton count={8} />
      </div>
    </div>
  );
}
