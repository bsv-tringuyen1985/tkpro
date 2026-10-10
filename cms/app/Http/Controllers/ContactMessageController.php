<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:contact_messages.view')->only(['index', 'show']);
        $this->middleware('permission:contact_messages.update')->only(['markRead', 'markUnread']);
        $this->middleware('permission:contact_messages.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = ContactMessage::orderByDesc('created_at');

        if ($request->get('filter') === 'unread') {
            $query->where('is_read', false);
        } elseif ($request->get('filter') === 'read') {
            $query->where('is_read', true);
        }

        if ($q = trim((string) $request->get('q', ''))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%'.$q.'%')
                    ->orWhere('phone', 'like', '%'.$q.'%')
                    ->orWhere('subject', 'like', '%'.$q.'%');
            });
        }

        $messages = $query->paginate(15)->withQueryString();
        $unreadCount = ContactMessage::where('is_read', false)->count();

        return view('contact_messages.index', compact('messages', 'unreadCount'));
    }

    /**
     * Xem chi tiết — tự động đánh dấu đã đọc.
     */
    public function show(ContactMessage $contactMessage)
    {
        $contactMessage->markAsRead();

        return view('contact_messages.show', ['message' => $contactMessage]);
    }

    public function markUnread(ContactMessage $contactMessage)
    {
        $contactMessage->markAsUnread();

        return back()->with('success', 'Đã đánh dấu tin nhắn là chưa đọc.');
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return redirect()->route('admin.contact_messages.index')
            ->with('success', 'Đã xóa tin nhắn liên hệ.');
    }
}
