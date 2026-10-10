"use client";

import { useRouter } from "next/navigation";
import { useState, type FormEvent } from "react";

export default function SearchBox({
  initialQuery = "",
  onNavigate,
  compact = false,
}: {
  initialQuery?: string;
  onNavigate?: () => void;
  compact?: boolean;
}) {
  const router = useRouter();
  const [q, setQ] = useState(initialQuery);

  const submit = (e: FormEvent) => {
    e.preventDefault();
    const value = q.trim();
    if (!value) return;
    onNavigate?.();
    router.push(`/tim-kiem?q=${encodeURIComponent(value)}`);
  };

  return (
    <form onSubmit={submit} role="search" className="flex w-full gap-2">
      <input
        type="search"
        value={q}
        onChange={(e) => setQ(e.target.value)}
        placeholder="Tìm camera, linh kiện, bài viết…"
        aria-label="Tìm kiếm"
        className={`w-full rounded-full border border-line bg-cream px-5 text-sm text-ink outline-hidden placeholder:text-muted focus:border-brand ${
          compact ? "py-2.5" : "py-3.5"
        }`}
      />
      <button
        type="submit"
        className={`shrink-0 rounded-full bg-brand font-bold text-white hover:bg-brand-dark ${
          compact ? "px-4 py-2.5 text-sm" : "px-6 py-3.5 text-sm"
        }`}
      >
        Tìm
      </button>
    </form>
  );
}
