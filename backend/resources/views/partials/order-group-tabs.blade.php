{{-- The orders directory by category, as the panel asked: Upcoming, Ongoing,
     Pickup and For Delivery, then Completed and Cancelled so every order is
     under one tab (App\Models\Order::GROUPS), each with how many it holds.

     Two modes. The admin and staff lists page on the server, so there each
     tab is a link that keeps the search and date but drops a status the new
     tab doesn't hold; pass $group (the open tab's key, '' for All),
     $groupCounts (OrderList::groupCounts) and $route. The customer's list
     filters in the browser, so with $clientSide each tab is a button carrying
     data-group-tab for the page script, and $group is ''. --}}
@php
    $clientSide = $clientSide ?? false;
    $tabQuery = $clientSide ? [] : request()->except(['group', 'status', 'page']);
    $tabs = ['' => ['label' => 'All', 'icon' => 'bi-collection', 'hint' => 'Every order']] + \App\Models\Order::GROUPS;
@endphp
<nav class="order-group-tabs mb-3" aria-label="Order categories">
    @foreach($tabs as $key => $meta)
        @php $active = $group === $key; @endphp
        @if($clientSide)
            <button type="button" class="order-group-tab {{ $active ? 'active' : '' }}" title="{{ $meta['hint'] }}"
                data-group-tab="{{ $key }}" aria-pressed="{{ $active ? 'true' : 'false' }}">
        @else
            <a href="{{ route($route, $key === '' ? $tabQuery : $tabQuery + ['group' => $key]) }}"
                class="order-group-tab {{ $active ? 'active' : '' }}" title="{{ $meta['hint'] }}"
                @if($active) aria-current="page" @endif>
        @endif
            <i class="bi {{ $meta['icon'] }}"></i>
            <span>{{ $meta['label'] }}</span>
            <span class="order-group-count">{{ $groupCounts[$key === '' ? 'all' : $key] ?? 0 }}</span>
        {!! $clientSide ? '</button>' : '</a>' !!}
    @endforeach
</nav>

@once
    <style>
        /* One row of pill tabs; on a narrow screen it scrolls sideways
           rather than wrapping into a second row. */
        .order-group-tabs {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 2px;
            scrollbar-width: thin;
        }
        .order-group-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            flex-shrink: 0;
            padding: 0.5rem 0.9rem;
            border: 1px solid #e3e7ec;
            border-radius: 999px;
            background: #fff;
            color: #0e2e45;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(14, 46, 69, 0.04);
            transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }
        .order-group-tab:hover { border-color: #0e2e45; color: #0e2e45; }
        .order-group-tab.active { background: #0e2e45; border-color: #0e2e45; color: #fff; }
        .order-group-count {
            min-width: 1.5rem;
            padding: 0.1rem 0.45rem;
            border-radius: 999px;
            background: rgba(14, 46, 69, 0.08);
            font-size: 0.72rem;
            font-weight: 700;
            text-align: center;
        }
        .order-group-tab.active .order-group-count { background: #ffc508; color: #0e2e45; }
    </style>
@endonce
