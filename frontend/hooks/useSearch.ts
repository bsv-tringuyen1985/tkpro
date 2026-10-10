"use client";

import { useEffect, useState } from "react";
import { searchAll } from "@/services/searchService";
import type { SearchResult } from "@/types";

export type SearchState =
  | { status: "idle" }
  | { status: "loading" }
  | { status: "error"; message: string }
  | { status: "done"; result: SearchResult };

/** Hook gọi GET /search theo từ khóa, có hủy request khi đổi query. */
export function useSearch(q: string): SearchState {
  const [state, setState] = useState<SearchState>({ status: "idle" });

  useEffect(() => {
    const keyword = q.trim();
    if (!keyword) {
      setState({ status: "idle" });
      return;
    }
    let cancelled = false;
    setState({ status: "loading" });
    searchAll(keyword)
      .then((result) => {
        if (!cancelled) setState({ status: "done", result });
      })
      .catch(() => {
        if (!cancelled)
          setState({ status: "error", message: "Không thể tìm kiếm lúc này. Vui lòng thử lại." });
      });
    return () => {
      cancelled = true;
    };
  }, [q]);

  return state;
}
