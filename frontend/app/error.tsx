"use client";

import Link from "next/link";
import { useEffect } from "react";

/** Error boundary chung cho mọi route. */
export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    console.error("Route error:", error);
  }, [error]);

  return (
    <div className="mx-auto max-w-[1180px] px-4 py-24 text-center md:px-6">
      <div className="mx-auto grid h-20 w-20 place-items-center rounded-full bg-red-50 text-4xl">
        ⚠️
      </div>
      <h1 className="mt-6 font-serif text-3xl font-bold">Đã xảy ra lỗi</h1>
      <p className="mx-auto mt-3 max-w-md text-sm leading-relaxed text-muted">
        Rất tiếc, trang này đang gặp sự cố khi tải dữ liệu. Vui lòng thử lại sau ít phút.
      </p>
      <div className="mt-8 flex flex-wrap justify-center gap-3">
        <button
          type="button"
          onClick={() => reset()}
          className="rounded-full bg-brand px-6 py-3 text-sm font-bold text-white hover:bg-brand-dark"
        >
          Thử lại
        </button>
        <Link
          href="/"
          className="rounded-full border border-line px-6 py-3 text-sm font-bold hover:border-brand"
        >
          Về trang chủ
        </Link>
      </div>
    </div>
  );
}
