import ContactForm from "@/components/ContactForm";
import type { Settings } from "@/types";

type Props = {
  settings: Settings;
  /** Prefill ô "thiết bị quan tâm", vd từ trang chi tiết sản phẩm. */
  prefillSubject?: string;
  prefillProductName?: string;
  kicker?: string;
  titleA?: string;
  titleB?: string;
  description?: string;
  headingLevel?: "h1" | "h2";
};

/**
 * Khối liên hệ dùng chung (trang chủ + trang liên hệ):
 * thông tin từ settings + form POST /contact-messages.
 */
export default function ContactSection({
  settings,
  prefillSubject = "",
  prefillProductName = "",
  kicker = "Let's connect",
  titleA = "Giải pháp công nghệ",
  titleB = "cho bạn.",
  description,
  headingLevel = "h2",
}: Props) {
  const infos = [
    { k: "HOTLINE", v: settings.hotline, href: `tel:${settings.hotline.replace(/\s/g, "")}` },
    { k: "EMAIL", v: settings.email, href: `mailto:${settings.email}` },
    { k: "SHOWROOM", v: settings.address },
    { k: "THỜI GIAN", v: settings.working_hours },
  ];

  const Heading = headingLevel;

  return (
    <div className="grid gap-8 rounded-[28px] bg-linear-to-br from-brand-dark to-brand p-6 text-white md:grid-cols-2 md:p-10">
      <div>
        <span className="text-[11px] font-extrabold uppercase tracking-[2px] text-[#d8c18e]">{kicker}</span>
        <Heading className="mt-2 font-serif text-[34px] font-bold">
          {titleA}
          <br />
          {titleB}
        </Heading>
        <p className="mt-3 text-[13px] leading-[1.7] text-[#c8d9ce]">
          {description ??
            `Cần báo giá, tư vấn camera, lắp PC hoặc tìm linh kiện tương thích? Hãy để lại thông tin, đội ngũ ${settings.site_name} sẽ hỗ trợ nhanh chóng.`}
        </p>
        <div className="mt-6 grid gap-3.5 sm:grid-cols-2">
          {infos.map((it) => (
            <div key={it.k} className="rounded-2xl border border-white/20 bg-white/5 p-4">
              <small className="mb-1 block text-[10px] text-[#b9cec0]">{it.k}</small>
              {it.href ? (
                <a href={it.href} className="text-[13px] font-bold hover:underline">
                  {it.v}
                </a>
              ) : (
                <b className="text-[13px]">{it.v}</b>
              )}
            </div>
          ))}
        </div>
        {settings.google_maps_embed && (
          <div
            className="mt-6 overflow-hidden rounded-2xl"
            dangerouslySetInnerHTML={{ __html: settings.google_maps_embed }}
          />
        )}
      </div>
      <div>
        {prefillProductName && (
          <p className="mb-3 rounded-xl bg-white/10 px-4 py-3 text-[13px]">
            Bạn đang quan tâm: <b>{prefillProductName}</b>
          </p>
        )}
        <ContactForm prefillSubject={prefillSubject} />
      </div>
    </div>
  );
}
