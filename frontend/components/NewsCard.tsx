import Image from "next/image";
import Link from "next/link";
import type { Post } from "@/types";
import { absoluteImageUrl, formatDateVi, truncate } from "@/utils/format";

const EMOJI = ["📰", "💡", "🎁", "🖥️"];

/** Card tin tức theo reference: ảnh, chuyên mục + ngày, tiêu đề, trích dẫn, link. */
export default function NewsCard({ post, index = 0, large = false }: { post: Post; index?: number; large?: boolean }) {
  const img = absoluteImageUrl(post.featured_image);

  return (
    <article className="group overflow-hidden rounded-2xl border border-line bg-white transition-all hover:-translate-y-1 hover:shadow-card">
      <Link href={`/tin-tuc/${post.slug}`} className="block" aria-label={post.title}>
        <div className={`relative grid place-items-center overflow-hidden bg-linear-to-br from-[#e4f6e9] to-[#acd9b9] ${large ? "h-56 md:h-60" : "h-44"}`}>
          {img ? (
            <Image
              src={img}
              alt={post.title}
              fill
              sizes={large ? "(max-width: 768px) 100vw, 45vw" : "(max-width: 768px) 100vw, 25vw"}
              className="object-cover transition-transform duration-300 group-hover:scale-105"
              loading="lazy"
            />
          ) : (
            <span className="text-6xl" aria-hidden="true">
              {EMOJI[index % EMOJI.length]}
            </span>
          )}
        </div>
      </Link>
      <div className="p-5">
        <p className="text-[10px] uppercase tracking-wider text-[#849087]">
          {post.category?.name ?? "Tin tức"}
          {post.published_at ? ` · ${formatDateVi(post.published_at)}` : ""}
        </p>
        <Link href={`/tin-tuc/${post.slug}`}>
          <h3 className={`mt-2 font-serif font-bold leading-snug group-hover:text-brand ${large ? "text-2xl" : "text-lg"}`}>
            {post.title}
          </h3>
        </Link>
        {post.excerpt && (
          <p className="mt-2 line-clamp-3 text-[13px] leading-relaxed text-muted">
            {truncate(post.excerpt, 150)}
          </p>
        )}
        <Link href={`/tin-tuc/${post.slug}`} className="mt-3 inline-block text-xs font-extrabold text-brand">
          Đọc tiếp →
        </Link>
      </div>
    </article>
  );
}
