import { apiFetch, apiFetchWithMeta } from "@/lib/api";
import type { Post, PostCategory, ApiMeta } from "@/types";

export type PostListParams = {
  q?: string;
  category?: string; // slug danh mục bài viết
  per_page?: number;
  page?: number;
};

export async function getPosts(
  params: PostListParams = {},
): Promise<{ items: Post[]; meta: ApiMeta }> {
  const useMeta = params.per_page !== undefined || params.page !== undefined;
  if (useMeta) {
    const { data, meta } = await apiFetchWithMeta<Post[]>(`/posts`, params, {
      revalidate: 180,
    });
    return { items: data, meta: meta ?? {} };
  }
  const data = await apiFetch<Post[]>(`/posts`, params, { revalidate: 180 });
  return { items: data, meta: {} };
}

/** GET /api/v1/posts/{slug} — kèm category, author. */
export async function getPost(slug: string): Promise<Post> {
  const data = await apiFetch<{ post: Post; related: Post[] }>(`/posts/${encodeURIComponent(slug)}`, undefined, {
    revalidate: 180,
  });
  return { ...data.post, related: data.related };
}

/** GET /api/v1/post-categories. */
export async function getPostCategories(): Promise<PostCategory[]> {
  return apiFetch<PostCategory[]>(`/post-categories`, undefined, { revalidate: 300 });
}
