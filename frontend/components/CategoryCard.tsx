import Image from "next/image";
import Link from "next/link";
import type { ProductCategory } from "@/types";
import { absoluteImageUrl, truncate } from "@/utils/format";

const ICONS: Record<string, string> = {
  "camera-giam-sat": "🎥",
  "linh-kien-pc": "🖥️",
  "thiet-bi-dien-tu": "📱",
};

/** Card danh mục sản phẩm. */
export default function CategoryCard({ category }: { category: ProductCategory }) {
  const img = absoluteImageUrl(category.image);
  const icon = ICONS[category.slug] ?? "📦";

  return (
    <Link
      href={`/san-pham/${category.slug}`}
      className="group relative flex items-center gap-4 overflow-hidden rounded-2xl border border-line bg-white p-4 transition-all hover:-translate-y-1 hover:shadow-card"
    >
      <div className="relative grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-xl bg-linear-to-br from-brand-mint to-line text-3xl">
        {img ? (
          <Image src={img} alt={category.name} fill sizes="64px" className="object-cover" loading="lazy" />
        ) : (
          <span aria-hidden="true">{icon}</span>
        )}
      </div>
      <div className="min-w-0">
        <h3 className="truncate font-serif text-lg font-bold group-hover:text-brand">{category.name}</h3>
        {category.description && (
          <p className="mt-1 line-clamp-2 text-xs leading-relaxed text-muted">
            {truncate(category.description, 90)}
          </p>
        )}
        {typeof category.products_count === "number" && (
          <p className="mt-1 text-[11px] font-bold text-brand">{category.products_count} sản phẩm</p>
        )}
      </div>
      <span aria-hidden="true" className="ml-auto text-muted transition-transform group-hover:translate-x-1 group-hover:text-brand">
        →
      </span>
    </Link>
  );
}
