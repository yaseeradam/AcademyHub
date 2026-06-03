@extends('layouts.superadmin')

@section('header_title', 'System Alerts & Notifications')
@section('header_subtitle', 'Real-time payout settlement requests, marketplace ratings, and support tickets')

@section('header_actions')
    <form action="{{ route('superadmin.notifications.mark-all-read') }}" method="POST" style="display:inline-block; margin:0;">
        @csrf
        <button type="submit" class="sa-btn sa-btn-ghost">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            Mark All as Read
        </button>
    </form>
@endsection

@section('content')

@if(session('status'))
    <div class="sa-alert success" style="margin-bottom: 20px;">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>{{ session('status') }}</div>
    </div>
@endif

<div class="sa-panel">
    <div class="sa-panel-header" style="background: #f8fafc;">
        <span class="sa-panel-title">All System Notifications ({{ $notifications->total() }})</span>
    </div>

    <div style="display: flex; flex-direction: column;">
        @forelse($notifications as $n)
            @php
                $isUnread = is_null($n->read_at);
                $iconBg = 'linear-gradient(135deg, #6366f1, #4f46e5)'; // blue
                $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
                
                if ($n->type === 'payout_request') {
                    $iconBg = 'linear-gradient(135deg, #10b981, #059669)'; // green
                    $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
                } elseif ($n->type === 'app_rating') {
                    $iconBg = 'linear-gradient(135deg, #ff8c42, #fb923c)'; // orange
                    $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.371 1.24.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.17 0l-3.971 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 10.3c-.783-.57-.38-1.81.588-1.81h4.907a1 1 0 00.95-.69l1.52-4.674z" />';
                }
            @endphp

            <div onclick="window.location.href='{{ route('superadmin.notifications.open', $n) }}'" 
                 style="display: flex; gap: 16px; padding: 20px 24px; border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: all 0.15s; align-items: flex-start;
                        {!! $isUnread ? 'background: #faf5ff; border-left: 4px solid #7c3aed;' : 'background: white;' !!}"
                 onmouseover="this.style.background='{{ $isUnread ? '#f5ebff' : '#f8fafc' }}'"
                 onmouseout="this.style.background='{{ $isUnread ? '#faf5ff' : 'white' }}'">
                
                {{-- Icon Badge --}}
                <div style="width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: {!! $iconBg !!}; color: white;">
                    <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        {!! $iconSvg !!}
                    </svg>
                </div>

                {{-- Message Info --}}
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 6px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 11.5px; font-weight: 800; color: #7c3aed; text-transform: uppercase;">
                                {{ $n->tenant?->name ?? 'System' }}
                            </span>
                            @if($isUnread)
                                <span style="font-size: 9px; font-weight: 800; text-transform: uppercase; background: #ef4444; color: white; padding: 2px 6px; border-radius: 999px;">
                                    New
                                </span>
                            @endif
                        </div>
                        <span style="font-size: 11.5px; color: #94a3b8; font-weight: 500;">
                            {{ $n->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <div style="font-size: 15px; font-weight: 800; color: var(--sa-text); margin-top: 4px;">
                        {{ $n->title }}
                    </div>

                    <div style="font-size: 13.5px; color: #475569; margin-top: 6px; line-height: 1.5;">
                        {{ $n->message }}
                    </div>
                </div>

                {{-- Action indicator --}}
                @if($n->action_url)
                    <div class="hidden-xs" style="align-self: center; flex-shrink: 0; color: #94a3b8; transition: color 0.15s;">
                        <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                @endif
            </div>
        @empty
            <div style="text-align: center; padding: 56px 24px;">
                <div style="width: 54px; height: 54px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8; margin: 0 auto 16px;">
                    <svg style="width: 24px; height: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div style="font-size: 15px; font-weight: 700; color: #475569; margin-bottom: 8px;">All caught up!</div>
                <div style="font-size: 13px; color: #94a3b8;">You have no active system notifications.</div>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div style="padding: 16px 24px; border-top: 1px solid #f1f5f9;">
            {{ $notifications->links() }}
        </div>
    @endif
</div>

@endsection
