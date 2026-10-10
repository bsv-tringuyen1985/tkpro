import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { getPage } from "@/services/pageService";
import { buildMetadata } from "@/lib/metadata";
import Breadcrumb from "@/components/Breadcrumb";
import Link from "next/link";

export const revalidate = 300;

export async function generateMetadata(): Promise<Metadata> {
  try {
    const page = await getPage("dich-vu");
    return buildMetadata(page);
  } catch {
    return { title: "Dịch vụ" };
  }
}

const SERVICE_HIGHLIGHTS = [
  { icon: "💬", t: "Tư vấn cấu hình", d: "Lắng nghe nhu cầu, đề xuất cấu hình tối ưu trong ngân sách của bạn." },
  { icon: "🎥", t: "Lắp đặt camera", d: "Khảo sát tận nơi, thi công gọn gàng, bàn giao hệ thống hoàn chỉnh." },
  { icon: "🛡️", t: "Bảo hành & đổi trả", d: "Bảo hành chính hãng rõ ràng, hỗ trợ kỹ thuật nhanh chóng." },
  { icon: "🚚", t: "Giao hàng toàn quốc", d: "Đóng gói cẩn thận, giao nhanh trên toàn quốc." },
];

export default async function DichVuPage() {
  let page = null;
  try {
    page = await getPage("dich-vu");
  } catch {
    notFound();
  }

  return (
    <div className="mx-auto max-w-[1180px] px-4 py-10 md:px-6">
      <Breadcrumb items={[{ label: page.title }]} />
      <header className="mb-8 max-w-3xl">
        <span className="text-[11px] font-extrabold uppercase tracking-[2px] text-brand-light">
          Dịch vụ của chúng tôi
        </span>
        <h1 className="mt-2 font-serif text-4xl font-bold tracking-tight md:text-5xl">{page.title}</h1>
        {page.excerpt && <p className="mt-4 text-[15px] leading-relaxed text-muted">{page.excerpt}</p>}
      </header>

      <div className="mb-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {SERVICE_HIGHLIGHTS.map((s) => (
          <div key={s.t} className="rounded-2xl border border-line bg-white p-5">
            <div className="text-3xl" aria-hidden="true">{s.icon}</div>
            <h2 className="mt-3 font-serif text-lg font-bold">{s.t}</h2>
            <p className="mt-2 text-[13px] leading-relaxed text-muted">{s.d}</p>
          </div>
        ))}
      </div>

      {page.content && (
        <div className="prose-tkpro max-w-4xl" dangerouslySetInnerHTML={{ __html: page.content }} />
      )}

      <div className="mt-12 rounded-[28px] bg-linear-to-br from-brand-dark to-brand p-8 text-center text-white md:p-12">
        <h2 className="font-serif text-2xl font-bold md:text-3xl">Cần tư vấn dịch vụ phù hợp?</h2>
        <p className="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-[#c8d9ce]">
          Để lại thông tin, đội ngũ TKPRO sẽ liên hệ và đưa ra giải pháp tốt nhất cho bạn.
        </p>
        <Link
          href="/lien-he"
          className="mt-6 inline-flex items-center gap-2 rounded-full bg-white px-6 py-3.5 text-sm font-extrabold text-brand hover:bg-brand-mint"
        >
          Liên hệ ngay →
        </Link>
      </div>
    </div>
  );
}
