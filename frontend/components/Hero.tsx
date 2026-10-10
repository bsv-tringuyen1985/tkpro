import Image from "next/image";
import Link from "next/link";
import type { Banner } from "@/types";
import { absoluteImageUrl } from "@/utils/format";

/**
 * Hero bám sát reference: gradient xanh + chữ watermark TKPRO,
 * eyebrow, tiêu đề, phụ đề, 2 CTA, trust stats từ settings.
 */
export default function Hero({
  banner,
  settings,
}: {
  banner: Banner | null;
  settings: {
    hero_eyebrow: string;
    hero_title: string;
    hero_sub: string;
    trust_1_num: string;
    trust_1_label: string;
    trust_2_num: string;
    trust_2_label: string;
    trust_3_num: string;
    trust_3_label: string;
  };
}) {
  const title = banner?.title || settings.hero_title;
  const subtitle = banner?.subtitle || settings.hero_sub;
  const ctaText = banner?.button_text || "Xem sản phẩm";
  const ctaLink = banner?.link || "/san-pham";
  const desktopImage = banner?.image_desktop ? absoluteImageUrl(banner.image_desktop) : "";
  const mobileImage = banner?.image_mobile ? absoluteImageUrl(banner.image_mobile) : desktopImage;

  const stats = [
    { num: settings.trust_1_num, label: settings.trust_1_label },
    { num: settings.trust_2_num, label: settings.trust_2_label },
    { num: settings.trust_3_num, label: settings.trust_3_label },
  ];

  return (
    <section className="mx-auto mt-5 max-w-[1180px] px-3 md:px-6" aria-label="Giới thiệu">
      <div className="relative min-h-[475px] overflow-hidden rounded-[30px] bg-linear-to-br from-brand-darker via-brand to-brand-light text-white shadow-hero">
        {/* Họa tiết trang trí */}
        <div
          aria-hidden="true"
          className="absolute inset-0"
          style={{
            background:
              "radial-gradient(circle at 78% 25%, rgba(255,255,255,0.13) 0 14%, transparent 15%), repeating-linear-gradient(135deg, transparent 0 28px, rgba(255,255,255,0.03) 29px 30px)",
          }}
        />

        <div className="relative z-10 grid gap-8 p-8 md:grid-cols-[1.2fr_1fr] md:p-[62px]">
          <div>
            <span className="inline-flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-[2.2px] text-[#b8f1c9]">
              <span className="inline-block h-px w-6 bg-[#b8f1c9]" aria-hidden="true" />
              {settings.hero_eyebrow}
            </span>
            <h1 className="mt-5 text-[41px] font-black leading-[1.02] tracking-tight md:text-[58px]">
              {title}
            </h1>
            <p className="mt-5 max-w-[520px] text-[15px] leading-[1.75] text-[#e1f4e8]">{subtitle}</p>
            <div className="mt-7 flex flex-wrap gap-3">
              <Link
                href={ctaLink}
                className="inline-flex items-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-extrabold text-brand hover:bg-brand-mint"
              >
                {ctaText} <span aria-hidden="true">→</span>
              </Link>
              <Link
                href="/lien-he"
                className="inline-flex items-center gap-2 rounded-full border border-white/40 px-5 py-3 text-sm font-extrabold text-white hover:bg-white/10"
              >
                Tư vấn cấu hình
              </Link>
            </div>
            <dl className="mt-10 flex flex-wrap gap-7">
              {stats.map((s, i) => (
                <div key={i}>
                  <dt className="text-xl font-extrabold text-white">{s.num}</dt>
                  <dd className="mt-1 text-[11px] text-[#bfd1c5]">{s.label}</dd>
                </div>
              ))}
            </dl>
          </div>

          {/* Ảnh banner / watermark TKPRO */}
          <div className="relative hidden min-h-[280px] md:block" aria-hidden={!desktopImage}>
            <span
              aria-hidden="true"
              className="pointer-events-none absolute right-[8%] top-[18%] select-none text-[82px] font-black italic tracking-[-5px] text-white/[0.07]"
            >
              TKPRO
            </span>
            {desktopImage ? (
              <Image
                src={desktopImage}
                alt={title}
                fill
                sizes="(max-width: 768px) 100vw, 40vw"
                className="rounded-2xl object-cover shadow-2xl"
                priority
              />
            ) : (
              <span
                aria-hidden="true"
                className="absolute bottom-[24%] right-[18%] text-[125px] drop-shadow-[0_14px_12px_rgba(0,27,12,0.4)]"
              >
                📷
              </span>
            )}
          </div>
        </div>

        {/* Ảnh mobile */}
        {mobileImage && (
          <div className="relative z-10 px-8 pb-8 md:hidden">
            <Image
              src={mobileImage}
              alt={title}
              width={640}
              height={360}
              sizes="100vw"
              className="h-auto w-full rounded-2xl object-cover"
            />
          </div>
        )}
      </div>
    </section>
  );
}
