<tr>
    <td>
        <div class="font-semibold">{!! str_repeat('<span class="text-slate-300 mr-1">↳</span>', $depth) !!}{{ $item->title }}</div>
    </td>
    <td class="text-sm text-slate-600">{{ $item->type === 'page' ? '📄 Trang' : '🔗 URL' }}</td>
    <td class="text-sm text-slate-500 max-w-[260px] truncate">{{ $item->resolvedUrl() }}</td>
    <td class="text-center text-sm">{{ $item->sort_order }}</td>
    <td>
        <span class="text-xs font-bold rounded-full px-2.5 py-1 {{ $item->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
            {{ $item->is_active ? 'Đang hiện' : 'Đang tắt' }}
        </span>
    </td>
    <td class="text-right whitespace-nowrap">
        <div class="inline-flex gap-1.5">
            @if(has_permission('menus.update'))
            <a href="{{ route('admin.menu_items.edit', [$menu, $item]) }}" class="text-sm border border-slate-200 rounded-lg px-2.5 py-1 hover:bg-brand-50 text-brand-700 font-semibold">✏️ Sửa</a>
            @endif
            @if(has_permission('menus.delete'))
            <form method="POST" action="{{ route('admin.menu_items.destroy', [$menu, $item]) }}" data-confirm="Xóa mục &quot;{{ $item->title }}&quot;{{ $item->children->count() ? ' cùng các mục con' : '' }}?" class="inline">
                @csrf @method('DELETE')
                <button class="text-sm border border-red-200 rounded-lg px-2.5 py-1 hover:bg-red-50 text-danger font-semibold">🗑️ Xóa</button>
            </form>
            @endif
        </div>
    </td>
</tr>
