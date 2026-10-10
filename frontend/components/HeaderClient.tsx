"use client";

import Image from "next/image";
import Link from "next/link";
import { useState } from "react";
import Modal from "./Modal";
import SearchBox from "./SearchBox";
import type { MenuItem } from "@/types";
import { resolveMenuItemUrl } from "@/services/menuService";

/** Menu mặc định khi API chưa trả về (không bao giờ để thanh menu trống). */
const FALLBACK_MENU: MenuItem[] = [
  { id: -1, title: "Trang chủ", type: "url", url: "/", page_id: null, target: "_self", sort_order: 1, is_active: true },
  { id: -2, title: "Sản phẩm", type: "url", url: "/san-pham", page_id: null, target: "_self", sort_order: 2, is_active: true },
  { id: -3, title: "Tin tức", type: "url", url: "/tin-tuc", page_id: null, target: "_self", sort_order: 3, is_active: true },
  { id: -4, title: "Liên hệ", type: "url", url: "/lien-he", page_id: null, target: "_self", sort_order: 4, is_active: true },
];

function MenuLinks({ items, onClick }: { items: MenuItem[]; onClick?: () => void }) {
  return (
    <>
      {items.map((it) => (
        <Link
          key={it.id}
          href={resolveMenuItemUrl(it)}
          target={it.target === "_blank" ? "_blank" : undefined}
          rel={it.target === "_blank" ? "noopener noreferrer" : undefined}
          onClick={onClick}
          className="shrink-0 px-4 py-3 text-sm font-bold whitespace-nowrap text-ink transition-colors hover:bg-brand-mint hover:text-brand"
        >
          {it.title}
        </Link>
      ))}
    </>
  );
}

export default function HeaderClient({
  siteName,
  hotline,
  announcement,
  items,
}: {
  siteName: string;
  hotline: string;
  announcement: string;
  items: MenuItem[];
}) {
  const [menuOpen, setMenuOpen] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);

  // Luôn có menu để hiển thị: ưu tiên từ CMS, fallback khi API trống/lỗi.
  const navItems = items.length > 0 ? items : FALLBACK_MENU;

  return (
    <>
      {/* Topbar: thông báo + hotline từ settings */}
      <div className="bg-brand text-[13px] text-white">
        <div className="mx-auto flex h-[38px] max-w-[1180px] items-center justify-between px-4 md:px-6">
          <span className="truncate opacity-90">{announcement}</span>
          <a href={`tel:${hotline.replace(/\s/g, "")}`} className="ml-4 shrink-0 font-bold">
            Hotline: {hotline}
          </a>
        </div>
      </div>

      <header className="sticky top-0 z-40 border-b border-line bg-white/95 backdrop-blur">
        <div className="mx-auto flex h-[78px] max-w-[1180px] items-center gap-7 px-4 md:px-6">
          <Link href="/" className="mr-auto flex items-center" aria-label={siteName}>
            <Image src="/logo.svg" alt={siteName} width={170} height={46} priority className="h-11 w-auto md:h-[52px]" />
          </Link>

          <div className="flex items-center gap-2">
            <button
              type="button"
              onClick={() => setSearchOpen(true)}
              aria-label="Mở tìm kiếm"
              className="hidden h-10 w-10 items-center justify-center rounded-full border border-line bg-white text-lg hover:border-brand sm:inline-flex"
            >
              ⌕
            </button>
            <button
              type="button"
              onClick={() => setMenuOpen((v) => !v)}
              aria-label={menuOpen ? "Đóng menu" : "Mở menu"}
              aria-expanded={menuOpen}
              className="inline-flex h-10 w-10 items-center justify-center rounded-full border border-line bg-white text-lg lg:hidden"
            >
              {menuOpen ? "×" : "☰"}
            </button>
          </div>
        </div>

        {/* Thanh menu ngang: luôn hiển thị, cuộn ngang trên mobile */}
        <nav className="border-t border-line bg-white" aria-label="Điều hướng chính">
          <div className="mx-auto flex max-w-[1180px] items-center gap-1 overflow-x-auto px-2 md:justify-center md:px-6">
            <MenuLinks items={navItems} />
          </div>
        </nav>

        {/* Mobile menu (dropdown khi bấm hamburger) */}
        {menuOpen && (
          <nav className="border-t border-line bg-white lg:hidden" aria-label="Menu di động">
            <div className="mx-auto grid max-w-[1180px] gap-1 px-4 py-4 md:px-6">
              <div className="pt-2">
                <SearchBox compact onNavigate={() => setMenuOpen(false)} />
              </div>
            </div>
          </nav>
        )}
      </header>

      {/* Search modal */}
      <Modal open={searchOpen} onClose={() => setSearchOpen(false)} title="Tìm kiếm">
        <SearchBox onNavigate={() => setSearchOpen(false)} />
        <p className="mt-3 text-xs text-muted">
          Ví dụ: “camera wifi”, “RTX 4060”, “SSD NVMe”, “khuyến mãi”.
        </p>
      </Modal>
    </>
  );
}
