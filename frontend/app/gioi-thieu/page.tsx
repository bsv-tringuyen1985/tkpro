import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { getPage } from "@/services/pageService";
import { buildMetadata } from "@/lib/metadata";
import { stripHtml, truncate } from "@/utils/format";
import Breadcrumb from "@/components/Breadcrumb";

export const revalidate = 300;

export async function generateMetadata(): Promise<Metadata> {
  try {
    const page = await getPage("gioi-thieu");
    return buildMetadata(page);
  } catch {
    return { title: "Giới thiệu" };
  }
}

export default async function GioiThieuPage() {
  let page = null;
  try {
    page = await getPage("gioi-thieu");
  } catch {
    notFound();
  }

  return (
    <div className="mx-auto max-w-[1180px] px-4 py-10 md:px-6">
      <Breadcrumb items={[{ label: page.title }]} />
      <article>
        <header className="mb-8 max-w-3xl">
          <span className="text-[11px] font-extrabold uppercase tracking-[2px] text-brand-light">
            Về chúng tôi
          </span>
          <h1 className="mt-2 font-serif text-4xl font-bold tracking-tight md:text-5xl">{page.title}</h1>
          {page.excerpt && (
            <p className="mt-4 text-[15px] leading-relaxed text-muted">{page.excerpt}</p>
          )}
        </header>
        {page.content ? (
          <div
            className="prose-tkpro max-w-4xl"
            dangerouslySetInnerHTML={{ __html: page.content }}
          />
        ) : (
          <p className="text-muted">
            {truncate(stripHtml(page.excerpt), 400) || "Nội dung đang được cập nhật."}
          </p>
        )}
      </article>
    </div>
  );
}
