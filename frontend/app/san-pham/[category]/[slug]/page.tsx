import type { Metadata } from "next";
import { notFound } from "next/navigation";
import Link from "next/link";
import Breadcrumb from "@/components/Breadcrumb";
import ProductGallery from "@/components/ProductGallery";
import ProductGrid from "@/components/ProductGrid";
import ProductSpecifications from "@/components/ProductSpecifications";
import { getProduct } from "@/services/productService";
import { getSettings } from "@/services/settingService";
import { buildMetadata } from "@/lib/metadata";
import { fullPriceLabel, stripHtml, truncate } from "@/utils/format";
import type { Product } from "@/types";

export const revalidate = 120;

export async function generateMetadata({
  params,
}: {
  params: Promise<{ category: string; slug: string }>;
}): Promise<Metadata> {
  try {
    const p = await getProduct((await params).slug);
    return buildMetadata(p, { canonical: `/san-pham/${(await params).category}/${p.slug}` });
  } catch {
    return { title: "Chi tiết sản phẩm" };
  }
}

export default async function ProductDetailPage({
  params,
}: {
  params: Promise<{ category: string; slug: string }>;
}) {
  let product: Product;
  try {
    product = await getProduct((await params).slug);
  } catch {
    notFound();
  }

  const settings = await getSettings();
  const hotline = settings.hotline;
  const hotlineTel = `tel:${hotline.replace(/\s/g, "")}`;

  const catSlug = product.category?.slug ?? (await params).category;
  const catName = product.category?.name ?? "Sản phẩm";

  const productJsonLd = {
    "@context": "https://schema.org",
    "@type": "Product",
    name: product.name,
    description: product.short_description || truncate(stripHtml(product.description), 200),
    sku: product.sku || undefined,
    image: product.thumbnail || undefined,
    brand: { "@type": "Brand", name: "TKPRO" },
    ...(product.price !== null && product.price !== undefined
      ? {
          offers: {
            "@type": "Offer",
            priceCurrency: "VND",
            price: product.price,
            availability: "https://schema.org/InStock",
          },
        }
      : {}),
  };

  return (
    <div className="mx-auto max-w-[1180px] px-4 py-10 md:px-6">
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(productJsonLd) }} />
      <Breadcrumb
        items={[
          { label: "Sản phẩm", href: "/san-pham" },
          { label: catName, href: `/san-pham/${catSlug}` },
          { label: product.name },
        ]}
      />

      <div className="grid gap-10 lg:grid-cols-2">
        <ProductGallery product={product} />

        <div>
          <span className="text-[10px] font-extrabold uppercase tracking-wider text-[#829087]">
            TKPRO · {catName}
          </span>
          <h1 className="mt-2 font-serif text-3xl font-bold leading-tight tracking-tight md:text-4xl">
            {product.name}
          </h1>

          <div className="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-[13px] text-muted">
            {product.sku && (
              <span>
                Mã sản phẩm: <b className="text-ink">{product.sku}</b>
              </span>
            )}
            <span>
              Danh mục:{" "}
              <Link href={`/san-pham/${catSlug}`} className="font-bold text-brand hover:underline">
                {catName}
              </Link>
            </span>
          </div>

          {product.short_description && (
            <p className="mt-4 text-[15px] leading-relaxed text-muted">{product.short_description}</p>
          )}

          <div className="mt-6 rounded-2xl border border-line bg-white p-5">
            <p className="text-[11px] font-extrabold uppercase tracking-wider text-muted">Giá bán</p>
            <p className="mt-1 text-2xl font-extrabold text-brand">
              {fullPriceLabel(product.price, product.price_label)}
            </p>
            <p className="mt-1 text-xs text-muted">
              {product.price !== null ? "Giá tham khảo, có thể thay đổi theo thời điểm." : "Vui lòng liên hệ để nhận báo giá tốt nhất."}
            </p>
          </div>

          <div className="mt-6 flex flex-col gap-3 sm:flex-row">
            <Link
              href={`/lien-he?san-pham=${encodeURIComponent(product.name)}`}
              className="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-cta px-6 py-4 text-sm font-extrabold text-white hover:brightness-110"
            >
              Liên hệ tư vấn
            </Link>
            <a
              href={hotlineTel}
              className="inline-flex flex-1 items-center justify-center gap-2 rounded-full border border-brand px-6 py-4 text-sm font-extrabold text-brand hover:bg-brand hover:text-white"
            >
              📞 Gọi {hotline}
            </a>
          </div>

          <ul className="mt-6 space-y-2 text-[13px] text-muted">
            <li>✓ Hàng chính hãng, bảo hành rõ ràng</li>
            <li>✓ Tư vấn đúng nhu cầu, đúng ngân sách</li>
            <li>✓ Giao hàng toàn quốc</li>
          </ul>
        </div>
      </div>

      {/* Mô tả + thông số */}
      {(product.description || product.technical_specifications) && (
        <section className="mt-14" aria-label="Thông tin chi tiết">
          <div className="grid gap-10 lg:grid-cols-[1fr_1fr]">
            {product.description && (
              <div>
                <h2 className="mb-4 font-serif text-2xl font-bold">Mô tả sản phẩm</h2>
                <div className="prose-tkpro" dangerouslySetInnerHTML={{ __html: product.description }} />
              </div>
            )}
            {product.technical_specifications && (
              <div>
                <h2 className="mb-4 font-serif text-2xl font-bold">Thông số kỹ thuật</h2>
                <div className="overflow-hidden rounded-2xl border border-line bg-white">
                  <ProductSpecifications specs={product.technical_specifications} />
                </div>
              </div>
            )}
          </div>
        </section>
      )}

      {/* Sản phẩm liên quan */}
      {product.related && product.related.length > 0 && (
        <section className="mt-14" aria-label="Sản phẩm liên quan">
          <h2 className="mb-6 font-serif text-2xl font-bold md:text-3xl">Sản phẩm liên quan</h2>
          <ProductGrid products={product.related.slice(0, 4)} />
        </section>
      )}
    </div>
  );
}
