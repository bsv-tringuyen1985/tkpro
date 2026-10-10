import Link from "next/link";

/** 404 chung của website. */
export default function NotFound() {
  return (
    <div className="mx-auto max-w-[1180px] px-4 py-24 text-center md:px-6">
      <div className="mx-auto grid h-20 w-20 place-items-center rounded-full bg-brand-mint text-4xl">
        🔍
      </div>
      <h1 className="mt-6 font-serif text-4xl font-bold">Không tìm thấy trang</h1>
      <p className="mx-auto mt-3 max-w-md text-sm leading-relaxed text-muted">
        Trang bạn đang tìm có thể đã bị xóa, đổi tên hoặc tạm thời không khả dụng.
      </p>
      <div className="mt-8 flex flex-wrap justify-center gap-3">
        <Link
          href="/"
          className="rounded-full bg-brand px-6 py-3 text-sm font-bold text-white hover:bg-brand-dark"
        >
          Về trang chủ
        </Link>
        <Link
          href="/san-pham"
          className="rounded-full border border-line px-6 py-3 text-sm font-bold hover:border-brand"
        >
          Xem sản phẩm
        </Link>
      </div>
    </div>
  );
}
