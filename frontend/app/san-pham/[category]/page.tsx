import type { Metadata } from "next";
import { notFound } from "next/navigation";
import Breadcrumb from "@/components/Breadcrumb";
import ProductGrid from "@/components/ProductGrid";
import Pagination from "@/components/Pagination";
import { EmptyState } from "@/components/EmptyError";
import type { ApiMeta } from "@/types";
import { getProducts, getCategory } from "@/services/productService";
import { buildMetadata } from "@/lib/metadata";

export const revalidate = 120;

export async function generateMetadata({
  params,
}: {
  params: Promise<{ category: string }>;
}): Promise<Metadata> {
  try {
    const cat = await getCategory((await params).category);
    return buildMetadata(cat);
  } catch {
    return { title: "Danh mục sản phẩm" };
  }
}

type SP = { [key: string]: string | string[] | undefined };

export default async function CategoryPage({
  params,
  searchParams,
}: {
  params: Promise<{ category: string }>;
  searchParams: Promise<SP>;
}) {
  let category = null;
  try {
    category = await getCategory((await params).category);
  } catch {
    notFound();
  }

  const query = await searchParams;
  const page = Math.max(1, parseInt(typeof query.page === "string" ? query.page : "1", 10) || 1);
  const res = await getProducts({ category: category.slug, page, per_page: 12 }).catch(() => ({
    items: [],
    meta: ({} as ApiMeta),
  }));

  const currentPage = res.meta.current_page ?? page;
  const lastPage = res.meta.last_page ?? 1;

  return (
    <div className="mx-auto max-w-[1180px] px-4 py-10 md:px-6">
      <Breadcrumb items={[{ label: "Sản phẩm", href: "/san-pham" }, { label: category.name }]} />
      <header className="mb-8">
        <span className="text-[11px] font-extrabold uppercase tracking-[2px] text-brand-light">
          Danh mục
        </span>
        <h1 className="mt-2 font-serif text-4xl font-bold tracking-tight md:text-5xl">{category.name}</h1>
        {category.description && (
          <p className="mt-3 max-w-2xl text-sm leading-relaxed text-muted">{category.description}</p>
        )}
      </header>

      {res.items.length > 0 ? (
        <>
          <ProductGrid products={res.items} />
          <Pagination
            basePath={`/san-pham/${category.slug}`}
            currentPage={currentPage}
            lastPage={lastPage}
            total={res.meta.total}
          />
        </>
      ) : (
        <EmptyState
          title="Chưa có sản phẩm trong danh mục này"
          description="Danh mục đang được cập nhật. Vui lòng quay lại sau."
          actionHref="/san-pham"
          actionLabel="Xem tất cả sản phẩm"
        />
      )}
    </div>
  );
}
