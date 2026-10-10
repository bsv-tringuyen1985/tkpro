<?php

namespace App\Providers;

use App\Models\ContactMessage;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (parse_url(config('app.url'), PHP_URL_SCHEME) === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Carbon::setLocale('vi');
        Paginator::useTailwind();

        // Số tin nhắn chưa đọc cho badge ở sidebar.
        View::composer('layouts.admin', function ($view) {
            $unread = 0;

            if (auth()->check() && auth()->user()->hasPermission('contact_messages.view')) {
                try {
                    $unread = ContactMessage::where('is_read', false)->count();
                } catch (\Throwable $e) {
                    $unread = 0;
                }
            }

            $view->with('unreadMessages', $unread);
        });
    }
}
