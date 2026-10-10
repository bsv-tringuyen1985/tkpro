import Link from "next/link";
import Hero from "@/components/Hero";
import HomeFeatured from "@/components/HomeFeatured";
import ProductGrid from "@/components/ProductGrid";
import NewsCard from "@/components/NewsCard";
import ContactSection from "@/features/contact/ContactSection";
import { EmptyState } from "@/components/EmptyError";
import type { ApiMeta } from "@/types";
import { getProducts } from "@/services/productService";
import { getPosts } from "@/services/postService";
import { getBanners } from "@/services/bannerService";
import { getSettings } from "@/services/settingService";

export const revalidate = 120;

function SectionHead({ kicker, title, href, linkLabel }: { kicker: string; title: string; href?: string; linkLabel?: string }) {
  return (
    <div className="mb-7 flex items-end justify-between gap-4">
      <div>
        <span className="text-[11px] font-extrabold uppercase tracking-[2px] text-brand-light">{kicker}</span>
        <h2 className="mt-2 font-serif text-[29px] font-bold tracking-tight md:text-[35px]">{title}</h2>
      </div>
      {href && linkLabel && (
        <Link href={href} className="hidden shrink-0 text-[13px] font-bold text-brand hover:underline md:inline-block">
          {linkLabel} →
        </Link>
      )}
    </div>
  );
}

export default async function HomePage() {
  const [settings, banners, featuredRes, pcRes, deviceRes, postsRes] = await Promise.all([
    getSettings(),
    getBanners().catch(() => []),
    getProducts({ featured: 1, per_page: 12 }).catch(() => ({ items: [], meta: ({} as ApiMeta) })),
    getProducts({ category: "linh-kien-pc", per_page: 4 }).catch(() => ({ items: [], meta: ({} as ApiMeta) })),
    getProducts({ category: "thiet-bi-dien-tu", per_page: 4 }).catch(() => ({ items: [], meta: ({} as ApiMeta) })),
    getPosts({ per_page: 3 }).catch(() => ({ items: [], meta: ({} as ApiMeta) })),
  ]);

  const siteName = settings.site_name;

  return (
    <>
      <Hero banner={banners[0] ?? null} settings={settings} />

      {/* Sản phẩm nổi bật */}
      <section className="mx-auto max-w-[1180px] px-4 py-[76px] md:px-6" aria-label="Sản phẩm nổi bật">
        <SectionHead kicker="Best sellers" title="Sản phẩm nổi bật" />
        {featuredRes.items.length > 0 ? (
          <HomeFeatured products={featuredRes.items} />
        ) : (
          <EmptyState
            title="Chưa có sản phẩm nổi bật"
            description="Danh sách sản phẩm đang được cập nhật. Vui lòng quay lại sau."
          />
        )}
      </section>

      {/* Camera story */}
      <section className="mx-auto max-w-[1180px] px-4 pb-[76px] md:px-6" aria-label="Giải pháp camera">
        <div className="grid items-stretch gap-7 md:grid-cols-2">
          <div className="relative min-h-[280px] overflow-hidden rounded-[28px] bg-linear-to-br from-[#e1f5e7] to-[#83c69b] md:min-h-[410px]" aria-hidden="true">
            <span className="absolute -top-[55px] -right-[60px] h-[340px] w-[340px] rounded-full bg-white/50" />
            <span className="absolute bottom-[11%] left-[16%] text-[120px] md:text-[145px]">📹</span>
          </div>
          <div className="px-1 py-4 md:px-3 md:py-8">
            <span className="text-[11px] font-extrabold uppercase tracking-[2px] text-brand-light">Security solutions</span>
            <h2 className="mt-2 font-serif text-[32px] font-bold tracking-tight md:text-[40px]">
              An tâm mọi lúc,
              <br />
              mọi nơi.
            </h2>
            <p className="mt-4 max-w-[520px] text-sm leading-[1.7] text-muted">
              Từ camera WiFi gia đình đến hệ thống giám sát chuyên nghiệp, {siteName} giúp bạn quan sát rõ nét,
              cảnh báo thông minh và quản lý từ xa.
            </p>
            <div className="my-6 grid gap-3.5 sm:grid-cols-2">
              {[
                { icon: "🎥", t: "Hình ảnh sắc nét", d: "Độ phân giải cao, quan sát ngày đêm." },
                { icon: "🌙", t: "Nhìn đêm thông minh", d: "Hỗ trợ hồng ngoại và màu ban đêm." },
                { icon: "📱", t: "Xem từ xa", d: "Theo dõi trực tiếp trên điện thoại." },
                { icon: "🛠️", t: "Lắp đặt chuẩn", d: "Khảo sát, thi công và bảo hành tận nơi." },
              ].map((c) => (
                <div key={c.t} className="rounded-2xl border border-line bg-white p-4 text-xs leading-relaxed text-muted">
                  <b className="mb-1 block text-sm text-ink">
                    {c.icon} {c.t}
                  </b>
                  {c.d}
                </div>
              ))}
            </div>
            <Link
              href="/san-pham/camera-giam-sat"
              className="inline-flex items-center gap-2 rounded-full bg-cta px-6 py-3.5 text-sm font-extrabold text-white hover:brightness-110"
            >
              Xem camera bán chạy
            </Link>
          </div>
        </div>
      </section>

      {/* Linh kiện máy tính */}
      <section className="mx-auto max-w-[1180px] px-4 pb-[76px] md:px-6" aria-label="Linh kiện máy tính">
        <SectionHead kicker="Build your PC" title="Linh kiện máy tính" href="/san-pham/linh-kien-pc" linkLabel="Xem tất cả" />
        {pcRes.items.length > 0 ? (
          <ProductGrid products={pcRes.items} />
        ) : (
          <EmptyState title="Chưa có sản phẩm" description="Danh mục linh kiện PC đang được cập nhật." />
        )}
      </section>

      {/* Thiết bị điện tử */}
      <section className="mx-auto max-w-[1180px] px-4 pb-[76px] md:px-6" aria-label="Thiết bị điện tử">
        <SectionHead kicker="Smart devices" title="Thiết bị điện tử" href="/san-pham/thiet-bi-dien-tu" linkLabel="Xem tất cả" />
        {deviceRes.items.length > 0 ? (
          <ProductGrid products={deviceRes.items} />
        ) : (
          <EmptyState title="Chưa có sản phẩm" description="Danh mục thiết bị điện tử đang được cập nhật." />
        )}
      </section>

      {/* Tin công nghệ & khuyến mãi */}
      <section className="mx-auto max-w-[1180px] px-4 pb-[76px] md:px-6" aria-label="Tin công nghệ và khuyến mãi">
        <SectionHead kicker="Tech insights" title="Tin công nghệ & khuyến mãi" href="/tin-tuc" linkLabel="Xem tất cả" />
        {postsRes.items.length > 0 ? (
          <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            {postsRes.items.map((p, i) => (
              <NewsCard key={p.id} post={p} index={i} large={i === 0} />
            ))}
          </div>
        ) : (
          <EmptyState title="Chưa có bài viết" description="Tin tức công nghệ đang được cập nhật." />
        )}
      </section>

      {/* Liên hệ */}
      <section className="mx-auto max-w-[1180px] px-4 pb-[76px] md:px-6" aria-label="Liên hệ">
        <ContactSection settings={settings} />
      </section>
    </>
  );
}
