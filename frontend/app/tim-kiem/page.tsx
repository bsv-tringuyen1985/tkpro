import type { Metadata } from "next";
import { Suspense } from "react";
import TimKiemClient from "@/components/TimKiemClient";

export const metadata: Metadata = { title: "Tìm kiếm" };

/** useSearchParams bắt buộc nằm trong Suspense boundary (Next.js). */
export default function TimKiemPage() {
  return (
    <Suspense fallback={<div className="mx-auto max-w-[1180px] px-4 py-10 md:px-6" aria-label="Đang tải trang tìm kiếm" />}>
      <TimKiemClient />
    </Suspense>
  );
}
