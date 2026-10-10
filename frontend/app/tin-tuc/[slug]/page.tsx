import type { Metadata } from "next";
import { notFound } from "next/navigation";
import Image from "next/image";
import Link from "next/link";
import Breadcrumb from "@/components/Breadcrumb";
import NewsCard from "@/components/NewsCard";
import { getPost } from "@/services/postService";
import { buildMetadata } from "@/lib/metadata";
import { absoluteImageUrl, formatDateVi, truncate, stripHtml } from "@/utils/format";

export const revalidate = 180;

export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>;
}): Promise<Metadata> {
  try {
    const post = await getPost((await params).slug);
    return buildMetadata(post, { canonical: `/tin-tuc/${post.slug}` });
  } catch {
    return { title: "Chi tiết tin tức" };
  }
}

export default async function PostDetailPage({ params }: { params: Promise<{ slug: string }> }) {
  let post;
  try {
    post = await getPost((await params).slug);
  } catch {
    notFound();
  }

  const related = post.related ?? [];

  const img = absoluteImageUrl(post.featured_image);

  const articleJsonLd = {
    "@context": "https://schema.org",
    "@type": "Article",
    headline: post.title,
    description: post.excerpt || truncate(stripHtml(post.content), 200),
    image: img || undefined,
    datePublished: post.published_at || undefined,
    author: post.author ? { "@type": "Person", name: post.author.name } : undefined,
  };

  return (
    <div className="mx-auto max-w-[1180px] px-4 py-10 md:px-6">
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(articleJsonLd) }} />
      <Breadcrumb items={[{ label: "Tin tức", href: "/tin-tuc" }, { label: post.title }]} />

      <article className="mx-auto max-w-3xl">
        <header>
          <p className="text-[11px] font-extrabold uppercase tracking-[2px] text-brand-light">
            {post.category?.name ?? "Tin tức"}
          </p>
          <h1 className="mt-3 font-serif text-3xl font-bold leading-tight tracking-tight md:text-5xl">
            {post.title}
          </h1>
          <div className="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-[13px] text-muted">
            {post.published_at && <time dateTime={post.published_at}>{formatDateVi(post.published_at)}</time>}
            {post.author && <span>Tác giả: {post.author.name}</span>}
          </div>
          {post.excerpt && (
            <p className="mt-4 border-l-4 border-brand bg-brand-mint px-5 py-3 text-[15px] leading-relaxed text-ink">
              {post.excerpt}
            </p>
          )}
        </header>

        {img && (
          <figure className="relative mt-8 aspect-[16/9] overflow-hidden rounded-3xl">
            <Image
              src={img}
              alt={post.title}
              fill
              sizes="(max-width: 768px) 100vw, 768px"
              className="object-cover"
              priority
            />
          </figure>
        )}

        {post.content ? (
          <div className="prose-tkpro mt-8" dangerouslySetInnerHTML={{ __html: post.content }} />
        ) : (
          <p className="mt-8 text-muted">Nội dung bài viết đang được cập nhật.</p>
        )}

        <div className="mt-10 flex flex-wrap gap-3 border-t border-line pt-8">
          <Link
            href="/tin-tuc"
            className="rounded-full border border-line px-5 py-2.5 text-sm font-bold hover:border-brand"
          >
            ← Tất cả tin tức
          </Link>
          <Link
            href="/lien-he"
            className="rounded-full bg-cta px-5 py-2.5 text-sm font-extrabold text-white hover:brightness-110"
          >
            Nhận ưu đãi →
          </Link>
        </div>
      </article>

      {related.length > 0 && (
        <section className="mt-16" aria-label="Bài viết liên quan">
          <h2 className="mb-6 font-serif text-2xl font-bold md:text-3xl">Bài viết liên quan</h2>
          <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            {related.map((p, i) => (
              <NewsCard key={p.id} post={p} index={i} />
            ))}
          </div>
        </section>
      )}
    </div>
  );
}
