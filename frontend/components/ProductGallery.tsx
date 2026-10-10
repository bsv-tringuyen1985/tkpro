"use client";

import Image from "next/image";
import { useState } from "react";
import type { Product } from "@/types";
import { absoluteImageUrl } from "@/utils/format";

type Img = { src: string; alt: string };

/** Gallery chi tiết sản phẩm: ảnh chính + thumbnails (thumbnail + product_images). */
export default function ProductGallery({ product }: { product: Product }) {
  const images: Img[] = [];
  const thumb = absoluteImageUrl(product.thumbnail);
  if (thumb) images.push({ src: thumb, alt: product.name });
  for (const im of product.images ?? []) {
    const src = absoluteImageUrl(im.url);
    if (src && !images.some((x) => x.src === src)) {
      images.push({ src, alt: im.alt || product.name });
    }
  }
  const [active, setActive] = useState(0);
  const current = images[active] ?? images[0];

  if (images.length === 0) {
    return (
      <div className="grid aspect-square place-items-center rounded-3xl bg-linear-to-br from-brand-mint to-line text-8xl" aria-label={product.name}>
        <span aria-hidden="true">📦</span>
      </div>
    );
  }

  return (
    <div>
      <div className="relative aspect-square overflow-hidden rounded-3xl bg-linear-to-br from-[#eef9f1] to-[#d8f0df]">
        <Image
          key={current.src}
          src={current.src}
          alt={current.alt}
          fill
          sizes="(max-width: 768px) 100vw, 50vw"
          className="object-cover"
          priority
        />
        {product.featured && (
          <span className="absolute left-4 top-4 rounded-full bg-white px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wider text-brand">
            Hot
          </span>
        )}
      </div>

      {images.length > 1 && (
        <div className="mt-3 grid grid-cols-5 gap-2" role="tablist" aria-label="Ảnh sản phẩm">
          {images.map((im, i) => (
            <button
              key={im.src + i}
              type="button"
              role="tab"
              aria-selected={i === active}
              aria-label={`Xem ảnh ${i + 1}`}
              onClick={() => setActive(i)}
              className={`relative aspect-square overflow-hidden rounded-xl border-2 transition ${
                i === active ? "border-brand" : "border-transparent opacity-70 hover:opacity-100"
              }`}
            >
              <Image src={im.src} alt={im.alt} fill sizes="120px" className="object-cover" loading="lazy" />
            </button>
          ))}
        </div>
      )}
    </div>
  );
}
