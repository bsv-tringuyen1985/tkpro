export type RouteName = "home" | "products" | "category" | "product" | "posts" | "post" | "about" | "services" | "contact" | "search" | "missing";

export function matchRoute(pathname: string): { name: RouteName; params: { category: string; slug: string } } {
  const params = { category: "", slug: "" };
  let parts: string[];
  try {
    parts = pathname.replace(/^\/+|\/+$/g, "").split("/").filter(Boolean).map(decodeURIComponent);
  } catch { return { name: "missing", params }; }
  if (parts.some(part => part.includes("/") || part.includes("\\"))) return { name: "missing", params };
  if (parts.length === 0) return { name: "home", params };
  if (parts.length === 1) {
    const names: Record<string, RouteName> = {
      "san-pham": "products", "tin-tuc": "posts", "gioi-thieu": "about",
      "dich-vu": "services", "lien-he": "contact", "tim-kiem": "search",
    };
    return { name: Object.hasOwn(names, parts[0]) ? names[parts[0]] : "missing", params };
  }
  if (parts[0] === "san-pham" && parts.length <= 3) {
    params.category = parts[1];
    params.slug = parts[2] || "";
    return { name: parts.length === 3 ? "product" : "category", params };
  }
  if (parts[0] === "tin-tuc" && parts.length === 2) {
    params.slug = parts[1];
    return { name: "post", params };
  }
  return { name: "missing", params };
}

export function readSearchParams(search: string): Record<string, string | string[]> {
  const query = new URLSearchParams(search);
  const result: Record<string, string | string[]> = Object.create(null);
  for (const key of new Set(query.keys())) {
    const values = query.getAll(key);
    result[key] = values.length === 1 ? values[0] : values;
  }
  return result;
}
