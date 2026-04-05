@extends('layouts.superadmin')

@section('header_title', 'Schools')
@section('header_subtitle', 'Manage all school instances')

@section('header_actions')
    <a href="{{ route('superadmin.tenants.create') }}" class="px-4 py-2 bg-sky-500 hover:bg-sky-400 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-sky-500/20 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add New School
    </a>
@endsection

@section('content')
    <div class="glass-card rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/5 border-b border-white/10">
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest bg-white/5">School Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest bg-white/5">Domain / Slug</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest bg-white/5">Plan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest bg-white/5">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest bg-white/5">Limits (Students/Teachers)</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest bg-white/5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($tenants as $tenant)
                        <tr class="hover:bg-white/5 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="font-bold text-white text-base">{{ $tenant->name }}</div>
                                <div class="text-xs text-slate-400 mt-1 flex items-center gap-2">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    {{ $tenant->contact_email ?? 'No email' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($tenant->domain)
                                    <div class="text-sky-400 font-medium tracking-wide">{{ $tenant->domain }}</div>
                                @else
                                    <div class="text-slate-300 font-mono text-sm bg-black/20 px-2 py-1 rounded inline-block">{{ $tenant->slug }}</div>
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
                                    <span class="w-2 h-2 rounded-full shadow-[0_0_8px_currentColor] {{ $tenant->status === 'active' ? 'bg-emerald-400' : ($tenant->status === 'suspended' ? 'bg-rose-400' : 'bg-amber-400') }}"></span>
                                    {{ ucfirst($tenant->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-300 font-medium">{{ number_format($tenant->max_students) }} <span class="text-slate-500 text-xs">students</span></div>
                                <div class="text-sm text-slate-300 font-medium">{{ number_format($tenant->max_teachers) }} <span class="text-slate-500 text-xs">teachers</span></div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('superadmin.tenants.edit', $tenant) }}" class="p-2 rounded-lg bg-white/5 hover:bg-sky-500/20 text-slate-400 hover:text-sky-400 transition-colors tooltip" title="Edit School">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('superadmin.tenants.destroy', $tenant) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete this school instance?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-white/5 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 transition-colors" title="Delete School">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-white mb-2">No schools found</h3>
                                    <p class="text-sm max-w-sm">Get started by creating your first school instance in the system.</p>
                                    <a href="{{ route('superadmin.tenants.create') }}" class="mt-6 px-6 py-2.5 bg-sky-500 hover:bg-sky-400 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-sky-500/20">
                                        Create New School
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tenants->hasPages())
            <div class="px-6 py-4 border-t border-white/10 bg-black/20">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>
@endsection
