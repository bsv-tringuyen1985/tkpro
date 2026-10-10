/** Types khớp envelope API TKPRO: { success, message, data, meta?, errors? } */

export interface ApiMeta {
  current_page?: number;
  last_page?: number;
  per_page?: number;
  total?: number;
  [key: string]: unknown;
}

export interface ApiEnvelope<T> {
  success: boolean;
  message: string;
  data: T;
  meta?: ApiMeta;
  errors?: Record<string, string[]>;
}

export interface Paginated<T> {
  items: T[];
  meta: ApiMeta;
}

export interface SeoMeta {
  meta_title?: string | null;
  meta_description?: string | null;
  meta_keywords?: string | null;
  og_title?: string | null;
  og_description?: string | null;
  og_image?: string | null;
  canonical_url?: string | null;
  robots?: string | null;
}

export interface ProductCategory {
  seo?: SeoMeta | null;
  id: number;
  name: string;
  slug: string;
  description: string | null;
  image: string | null;
  parent_id: number | null;
  status: string;
  sort_order: number;
  products_count?: number;
  children?: ProductCategory[];
  meta_title?: string | null;
  meta_description?: string | null;
}

export interface ProductImage {
  id: number;
  product_id: number;
  path: string;
  url: string;
  filename: string;
  alt: string | null;
  sort_order: number;
}

export interface Product {
  seo?: SeoMeta | null;
  id: number;
  category_id: number;
  name: string;
  slug: string;
  sku: string | null;
  short_description: string | null;
  description: string | null;
  technical_specifications: string | Record<string, string> | Array<{ label: string; value: string }> | null;
  price: number | null;
  price_label: string | null;
  thumbnail: string | null;
  status: string;
  featured: boolean;
  sort_order: number;
  category?: ProductCategory;
  images?: ProductImage[];
  related?: Product[];
  meta_title?: string | null;
  meta_description?: string | null;
  meta_keywords?: string | null;
  created_at?: string;
}

export interface PostCategory {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  status: string;
  sort_order: number;
}

export interface PostAuthor {
  id: number;
  name: string;
}

export interface Post {
  seo?: SeoMeta | null;
  related?: Post[];
  id: number;
  category_id: number | null;
  title: string;
  slug: string;
  excerpt: string | null;
  content: string | null;
  featured_image: string | null;
  status: string;
  published_at: string | null;
  category?: PostCategory | null;
  author?: PostAuthor | null;
  meta_title?: string | null;
  meta_description?: string | null;
  meta_keywords?: string | null;
}

export interface Page {
  seo?: SeoMeta | null;
  id: number;
  title: string;
  slug: string;
  excerpt: string | null;
  content: string | null;
  featured_image: string | null;
  status: string;
  sort_order: number;
  meta_title?: string | null;
  meta_description?: string | null;
}

export interface MenuItem {
  id: number;
  title: string;
  type: "url" | "page";
  url: string | null;
  page_id: number | null;
  target: string;
  sort_order: number;
  is_active: boolean;
  children?: MenuItem[];
  /** URL đã resolve để render (page -> slug của page hoặc url thô) */
  resolved_url?: string;
  page_slug?: string | null;
}

export interface Menu {
  id: number;
  name: string;
  slug: string;
  location: "header" | "footer";
  items: MenuItem[];
}

export interface Banner {
  id: number;
  title: string;
  subtitle: string | null;
  image_desktop: string | null;
  image_mobile: string | null;
  link: string | null;
  button_text: string | null;
  sort_order: number;
  is_active: boolean;
}

export interface Settings {
  site_name: string;
  hotline: string;
  email: string;
  address: string;
  working_hours: string;
  announcement: string;
  facebook: string;
  youtube: string;
  zalo: string;
  google_maps_embed: string;
  copyright: string;
  footer_about: string;
  hero_eyebrow: string;
  hero_title: string;
  hero_sub: string;
  trust_1_num: string;
  trust_1_label: string;
  trust_2_num: string;
  trust_2_label: string;
  trust_3_num: string;
  trust_3_label: string;
  hours: string;
  [key: string]: string | undefined;
}

export interface ContactMessageInput {
  name: string;
  phone: string;
  email?: string;
  subject?: string;
  interest?: string;
  message?: string;
}

export interface SearchResult {
  products: Product[];
  posts: Post[];
}
