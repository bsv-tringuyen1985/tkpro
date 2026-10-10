<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'products' => Product::count(),
            'product_categories' => ProductCategory::count(),
            'posts' => Post::count(),
            'pages' => Page::count(),
            'users' => User::count(),
            'unread_messages' => ContactMessage::where('is_read', false)->count(),
        ];

        $latestProducts = Product::with('category')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Nội dung mới cập nhật gần đây (sản phẩm + bài viết).
        $recentProducts = Product::orderByDesc('updated_at')->limit(3)->get()->map(function ($item) {
            return [
                'type' => 'Sản phẩm',
                'title' => $item->name,
                'updated_at' => $item->updated_at,
                'url' => route('admin.products.edit', $item),
            ];
        });

        $recentPosts = Post::orderByDesc('updated_at')->limit(3)->get()->map(function ($item) {
            return [
                'type' => 'Bài viết',
                'title' => $item->title,
                'updated_at' => $item->updated_at,
                'url' => route('admin.posts.edit', $item),
            ];
        });

        $recentUpdates = $recentProducts
            ->concat($recentPosts)
            ->sortByDesc('updated_at')
            ->take(5)
            ->values();

        $unreadMessages = ContactMessage::where('is_read', false)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'latestProducts',
            'recentUpdates',
            'unreadMessages'
        ));
    }
}
