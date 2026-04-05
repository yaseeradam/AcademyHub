@extends('layouts.superadmin')

@section('header_title', 'Overview')
@section('header_subtitle', 'System health and tenant statistics')

@section('content')
    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Tenants -->
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-20 transition-transform group-hover:scale-110 group-hover:opacity-40">
                <svg class="w-16 h-16 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div class="relative z-10">
                <div class="text-slate-400 text-sm font-bold uppercase tracking-wider mb-2">Total Schools</div>
                <div class="text-4xl font-black text-white">{{ number_format($stats['total_tenants']) }}</div>
                <div class="mt-4 text-xs font-semibold text-sky-400 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Registered instances
                </div>
            </div>
        </div>

        <!-- Active Tenants -->
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-20 transition-transform group-hover:scale-110 group-hover:opacity-40">
                <svg class="w-16 h-16 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="relative z-10">
                <div class="text-slate-400 text-sm font-bold uppercase tracking-wider mb-2">Active Schools</div>
                <div class="text-4xl font-black text-white">{{ number_format($stats['active_tenants']) }}</div>
                <div class="mt-4 text-xs font-semibold text-emerald-400 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    Currently active
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-20 transition-transform group-hover:scale-110 group-hover:opacity-40">
                <svg class="w-16 h-16 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div class="relative z-10">
                <div class="text-slate-400 text-sm font-bold uppercase tracking-wider mb-2">Total Users</div>
                <div class="text-4xl font-black text-white">{{ number_format($stats['total_users']) }}</div>
                <div class="mt-4 text-xs font-semibold text-indigo-400 flex items-center gap-1">
                    Across all tenants
                </div>
            </div>
        </div>

        <!-- Super Admins -->
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-20 transition-transform group-hover:scale-110 group-hover:opacity-40">
                <svg class="w-16 h-16 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div class="relative z-10">
                <div class="text-slate-400 text-sm font-bold uppercase tracking-wider mb-2">Super Admins</div>
                <div class="text-4xl font-black text-white">{{ number_format($stats['total_superadmins']) }}</div>
                <div class="mt-4 text-xs font-semibold text-rose-400 flex items-center gap-1">
                    System administrators
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tenants -->
    <div class="glass-card rounded-3xl overflow-hidden">
        <div class="px-6 py-5 border-b border-white/10 flex items-center justify-between">
            <h2 class="text-lg font-black text-white">Recently Added Schools</h2>
            <a href="{{ route('superadmin.tenants.index') }}" class="text-sm font-bold text-sky-400 hover:text-sky-300 transition-colors">View All &rarr;</a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/5 border-b border-white/10">
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest">School Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest">Domain</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest">Plan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($recentTenants as $tenant)
                        <tr class="hover:bg-white/5 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="font-bold text-white">{{ $tenant->name }}</div>
                                <div class="text-xs text-slate-400 font-medium font-mono">{{ $tenant->slug }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($tenant->domain)
                                    <a href="https://{{ $tenant->domain }}" target="_blank" class="text-sky-400 hover:underline text-sm font-medium">{{ $tenant->domain }}</a>
                                @else
                                    <span class="text-slate-500 text-sm italic">Subdomain only</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider
                                    {{ $tenant->plan === 'enterprise' ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30' : 
                                      ($tenant->plan === 'pro' ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30' : 'bg-slate-500/20 text-slate-300 border border-slate-500/30') }}">
                                    {{ $tenant->plan }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="flex items-center gap-1.5 text-sm font-bold
                                    {{ $tenant->status === 'active' ? 'text-emerald-400' : 
                                      ($tenant->status === 'suspended' ? 'text-rose-400' : 'text-amber-400') }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $tenant->status === 'active' ? 'bg-emerald-400' : ($tenant->status === 'suspended' ? 'bg-rose-400' : 'bg-amber-400') }}"></span>
                                    {{ ucfirst($tenant->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-400 font-medium">
                                {{ $tenant->created_at->format('M j, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-slate-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <p class="text-sm font-semibold">No schools have been created yet.</p>
                                    <a href="{{ route('superadmin.tenants.create') }}" class="mt-4 px-4 py-2 bg-sky-500 hover:bg-sky-400 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-sky-500/20">
                                        + Create First School
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
