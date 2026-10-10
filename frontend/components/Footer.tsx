import Image from "next/image";
import Link from "next/link";
import { getMenu, resolveMenuItemUrl } from "@/services/menuService";
import { getSettings } from "@/services/settingService";
import type { MenuItem } from "@/types";

/** Server component: footer lấy menu footer + settings từ API. */
export default async function Footer() {
  const [menu, settings] = await Promise.all([
    getMenu("footer").catch(() => null),
    getSettings(),
  ]);

  const columns = groupFooterColumns(menu?.items ?? []);

  return (
    <footer className="px-4 pb-7 pt-10 md:px-6">
      <div className="mx-auto max-w-[1180px]">
        <div className="grid grid-cols-2 gap-6 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
          <div className="col-span-2 md:col-span-1">
            <Link href="/" aria-label={settings.site_name}>
              <Image src="/logo.svg" alt={settings.site_name} width={150} height={48} className="h-12 w-auto" loading="lazy" />
            </Link>
            <p className="mt-4 max-w-xs text-[12px] leading-relaxed text-[#77837b]">
              {settings.footer_about}
            </p>
            <div className="mt-4 flex gap-2 text-[12px]">
              {settings.facebook && settings.facebook !== "#" && (
                <a href={settings.facebook} target="_blank" rel="noopener noreferrer" className="rounded-full border border-line px-3 py-1.5 hover:border-brand hover:text-brand">
                  Facebook
                </a>
              )}
              {settings.youtube && settings.youtube !== "#" && (
                <a href={settings.youtube} target="_blank" rel="noopener noreferrer" className="rounded-full border border-line px-3 py-1.5 hover:border-brand hover:text-brand">
                  Youtube
                </a>
              )}
              {settings.zalo && settings.zalo !== "#" && (
                <a href={settings.zalo} target="_blank" rel="noopener noreferrer" className="rounded-full border border-line px-3 py-1.5 hover:border-brand hover:text-brand">
                  Zalo
                </a>
              )}
            </div>
          </div>

          {columns.map((col, i) => (
            <nav key={i} aria-label={col.title}>
              <h4 className="mb-3 text-xs font-extrabold uppercase tracking-wider">{col.title}</h4>
              <ul className="space-y-1">
                {col.items.map((it) => (
                  <li key={it.id}>
                    <Link
                      href={resolveMenuItemUrl(it)}
                      target={it.target === "_blank" ? "_blank" : undefined}
                      rel={it.target === "_blank" ? "noopener noreferrer" : undefined}
                      className="text-[12px] leading-8 text-[#77837b] hover:text-brand"
                    >
                      {it.title}
                    </Link>
                  </li>
                ))}
              </ul>
            </nav>
          ))}
        </div>

        <div className="mt-7 border-t border-line pt-5 text-[11px] text-[#8a958e]">
          {settings.copyright}
        </div>
      </div>
    </footer>
  );
}

type FooterColumn = { title: string; items: MenuItem[] };

/** Nhóm menu footer thành các cột: item cha -> cột, item con -> liên kết. */
function groupFooterColumns(items: MenuItem[]): FooterColumn[] {
  if (items.length === 0) return fallbackColumns();

  const withChildren = items.filter((it) => (it.children ?? []).length > 0);
  if (withChildren.length > 0) {
    return withChildren.slice(0, 4).map((it) => ({
      title: it.title,
      items: (it.children ?? []).filter((c) => c.is_active !== false),
    }));
  }

  // Menu phẳng: chia đều thành tối đa 3 cột, cột lấy tiêu đề từ mục đầu tiên.
  const cols: FooterColumn[] = [];
  const per = Math.max(1, Math.ceil(items.length / 3));
  for (let i = 0; i < items.length && cols.length < 3; i += per) {
    const chunk = items.slice(i, i + per);
    cols.push({ title: chunk[0]?.title ?? "Liên kết", items: chunk });
  }
  return cols;
}

function fallbackColumns(): FooterColumn[] {
  const link = (id: number, title: string, url: string): MenuItem => ({
    id, title, type: "url", url, page_id: null, target: "_self",
    sort_order: id, is_active: true,
  });
  return [
    {
      title: "Sản phẩm",
      items: [
        link(11, "Camera", "/san-pham/camera-giam-sat"),
        link(12, "Linh kiện PC", "/san-pham/linh-kien-pc"),
        link(13, "Thiết bị điện tử", "/san-pham/thiet-bi-dien-tu"),
      ],
    },
    {
      title: "Dịch vụ",
      items: [
        link(21, "Tư vấn cấu hình", "/lien-he"),
        link(22, "Lắp đặt camera", "/lien-he"),
        link(23, "Bảo hành & đổi trả", "/dich-vu"),
      ],
    },
    {
      title: "TKPRO",
      items: [
        link(31, "Về chúng tôi", "/gioi-thieu"),
        link(32, "Tin công nghệ", "/tin-tuc"),
        link(33, "Liên hệ", "/lien-he"),
      ],
    },
  ];
}
