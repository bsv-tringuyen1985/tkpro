import type { Metadata } from "next";
import Breadcrumb from "@/components/Breadcrumb";
import ProductFilters from "@/components/ProductFilters";
import ProductGrid from "@/components/ProductGrid";
import Pagination from "@/components/Pagination";
import { EmptyState } from "@/components/EmptyError";
import type { ApiMeta } from "@/types";
import { getProducts, getCategories } from "@/services/productService";

export const revalidate = 120;

export const metadata: Metadata = { title: "Sản phẩm" };

type SP = { [key: string]: string | string[] | undefined };

export default async function SanPhamPage({ searchParams }: { searchParams: Promise<SP> }) {
  const query = await searchParams;
  const q = typeof query.q === "string" ? query.q : undefined;
  const category = typeof query.category === "string" ? query.category : undefined;
  const sort = typeof query.sort === "string" ? query.sort : undefined;
  const page = Math.max(1, parseInt(typeof query.page === "string" ? query.page : "1", 10) || 1);

  const [res, categories] = await Promise.all([
    getProducts({ q, category, sort, page, per_page: 12 }).catch(() => ({ items: [], meta: ({} as ApiMeta) })),
    getCategories().catch(() => []),
  ]);

  const currentPage = res.meta.current_page ?? page;
  const lastPage = res.meta.last_page ?? 1;
  const total = res.meta.total;

  return (
    <div className="mx-auto max-w-[1180px] px-4 py-10 md:px-6">
      <Breadcrumb items={[{ label: "Sản phẩm" }]} />
      <header className="mb-8">
        <span className="text-[11px] font-extrabold uppercase tracking-[2px] text-brand-light">
          Catalogue
        </span>
        <h1 className="mt-2 font-serif text-4xl font-bold tracking-tight md:text-5xl">Sản phẩm</h1>
        <p className="mt-3 max-w-2xl text-sm leading-relaxed text-muted">
          Thiết bị điện tử, camera an ninh và linh kiện máy tính chính hãng — tư vấn đúng nhu cầu,
          bảo hành rõ ràng.
        </p>
      </header>

      <ProductFilters categories={categories} current={{ q, category, sort }} />

      {res.items.length > 0 ? (
        <>
          <ProductGrid products={res.items} />
          <Pagination
            basePath="/san-pham"
            currentPage={currentPage}
            lastPage={lastPage}
            total={total}
            extra={{ q, category, sort }}
          />
        </>
      ) : (
        <EmptyState
          title={q || category ? "Không tìm thấy sản phẩm phù hợp" : "Chưa có sản phẩm"}
          description="Thử thay đổi từ khóa hoặc danh mục khác, hoặc quay lại sau."
          actionHref="/san-pham"
          actionLabel="Xem tất cả sản phẩm"
        />
      )}
    </div>
  );
}
