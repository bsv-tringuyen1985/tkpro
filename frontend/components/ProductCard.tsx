import Image from "next/image";
import Link from "next/link";
import type { Product } from "@/types";
import { absoluteImageUrl, priceLabel, truncate } from "@/utils/format";

const GRADIENTS = [
  "from-[#eef9f1] to-[#d8f0df]",
  "from-[#eef9f1] to-[#d2ebdb]",
  "from-[#f2faef] to-[#dbeed9]",
  "from-[#fff0ed] to-[#f5d9d3]",
];

export function productHref(p: Product): string {
  const cat = p.category?.slug ?? "san-pham";
  return `/san-pham/${cat}/${p.slug}`;
}

/** Card sản phẩm theo reference: tag danh mục, ảnh, mô tả, giá, nút "Liên hệ tư vấn". */
export default function ProductCard({ product, index = 0 }: { product: Product; index?: number }) {
  const img = absoluteImageUrl(product.thumbnail);
  const gradient = GRADIENTS[index % GRADIENTS.length];
  const categoryLabel = product.category?.name ?? "TKPRO";

  return (
    <article className="group relative flex flex-col overflow-hidden rounded-2xl border border-line bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-card">
      <Link href={productHref(product)} className="block" aria-label={product.name}>
        <div className={`relative grid h-40 place-items-center overflow-hidden bg-linear-to-br md:h-56 ${gradient}`}>
          {img ? (
            <Image
              src={img}
              alt={product.name}
              fill
              sizes="(max-width: 768px) 50vw, 25vw"
              className="object-cover transition-transform duration-300 group-hover:scale-105"
              loading="lazy"
            />
          ) : (
            <span className="text-5xl md:text-7xl" aria-hidden="true">
              📦
            </span>
          )}
          <span className="absolute left-3 top-3 z-10 rounded-full bg-white px-2.5 py-1.5 text-[9px] font-extrabold uppercase tracking-wider text-brand">
            {product.featured ? `Hot · ${categoryLabel}` : categoryLabel}
          </span>
        </div>
      </Link>

      <div className="flex flex-1 flex-col p-3 md:p-4">
        <span className="text-[10px] font-extrabold uppercase tracking-wider text-[#829087]">
          TKPRO · {categoryLabel}
        </span>
        <Link href={productHref(product)}>
          <h3 className="mt-1.5 font-serif text-[15px] font-bold leading-snug md:text-lg group-hover:text-brand">
            {product.name}
          </h3>
        </Link>
        {product.short_description && (
          <p className="mt-2 line-clamp-2 text-xs leading-relaxed text-muted">
            {truncate(product.short_description, 110)}
          </p>
        )}
        <div className="mt-auto pt-3">
          <div className="text-[15px] font-extrabold text-brand">
            {priceLabel(product.price, product.price_label)}
          </div>
          <Link
            href={`/lien-he?san-pham=${encodeURIComponent(product.name)}`}
            className="mt-3 block w-full rounded-[10px] border border-[#cfe3d6] bg-[#f7faf7] px-3 py-2.5 text-center text-[13px] font-bold text-brand transition-colors hover:border-brand hover:bg-brand hover:text-white"
          >
            Liên hệ tư vấn
          </Link>
        </div>
      </div>
    </article>
  );
}
