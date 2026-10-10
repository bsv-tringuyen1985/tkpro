<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\Response;

/** Sitemap XML và robots.txt — route web của API (không nằm trong /api/v1). */
class SitemapController extends Controller
{
    public function index(): Response
    {
        $base = rtrim((string) config('app.frontend_url'), '/');

        $urls = [];
        $urls[] = $this->url($base.'/', now()->toAtomString());

        $products = Product::published()->with('category')->select('id', 'slug', 'category_id', 'updated_at')->get();
        foreach ($products as $product) {
            $categorySlug = $product->category?->slug ?? 'san-pham';
            $urls[] = $this->url("{$base}/san-pham/{$categorySlug}/{$product->slug}", $product->updated_at->toAtomString());
        }

        $posts = Post::published()->select('slug', 'updated_at')->get();
        foreach ($posts as $post) {
            $urls[] = $this->url("{$base}/tin-tuc/{$post->slug}", $post->updated_at->toAtomString());
        }

        $pages = Page::published()->select('slug', 'updated_at')->get();
        foreach ($pages as $page) {
            $path = $page->slug === 'trang-chu' ? '/' : '/'.$page->slug;
            $urls[] = $this->url($base.$path, $page->updated_at->toAtomString());
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n"
            .implode("\n", $urls)."\n"
            .'</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    public function robots(): Response
    {
        $base = rtrim((string) config('app.frontend_url'), '/');

        $content = "User-agent: *\nAllow: /\nSitemap: {$base}/sitemap.xml\n";

        return response($content, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    private function url(string $loc, string $lastmod): string
    {
        $loc = htmlspecialchars($loc, ENT_XML1, 'UTF-8');

        return "  <url><loc>{$loc}</loc><lastmod>{$lastmod}</lastmod></url>";
    }
}
