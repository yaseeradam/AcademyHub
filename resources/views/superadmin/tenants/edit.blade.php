@extends('layouts.superadmin')

@section('header_title', 'Edit School: ' . $tenant->name)
@section('header_subtitle', 'Update instance settings and subscription limits')

@section('header_actions')
    <a href="{{ route('superadmin.tenants.index') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-sm font-bold rounded-xl transition-colors border border-white/10">
        Back to List
    </a>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('superadmin.tenants.update', $tenant) }}" method="POST" class="glass-card rounded-3xl p-8">
            @csrf
            @method('PUT')
            
            <h2 class="text-xl font-black text-white border-b border-white/10 pb-4 mb-6">School Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">School Name <span class="text-rose-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required 
                           class="w-full glass-input rounded-xl px-4 py-3 placeholder-slate-500">
                    @error('name')<p class="text-rose-400 text-xs mt-2 font-semibold">{{ $message }}</p>@enderror
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Custom Domain <span class="text-slate-500 font-normal">(Optional)</span></label>
                    <input type="text" name="domain" value="{{ old('domain', $tenant->domain) }}" 
                           class="w-full glass-input rounded-xl px-4 py-3 placeholder-slate-500">
                    @error('domain')<p class="text-rose-400 text-xs mt-2 font-semibold">{{ $message }}</p>@enderror
                    <p class="text-slate-500 text-xs mt-2">Currently using slug: <span class="font-mono text-slate-400">{{ $tenant->slug }}</span></p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Contact Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $tenant->contact_email) }}" 
                           class="w-full glass-input rounded-xl px-4 py-3 placeholder-slate-500">
                    @error('contact_email')<p class="text-rose-400 text-xs mt-2 font-semibold">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $tenant->contact_phone) }}" 
                           class="w-full glass-input rounded-xl px-4 py-3 placeholder-slate-500">
                    @error('contact_phone')<p class="text-rose-400 text-xs mt-2 font-semibold">{{ $message }}</p>@enderror
                </div>
            </div>

            <h2 class="text-xl font-black text-white border-b border-white/10 pb-4 mb-6">Subscription & Limits</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Pricing Plan <span class="text-rose-400">*</span></label>
                    <div class="relative">
                        <select name="plan" required class="w-full glass-input rounded-xl px-4 py-3 appearance-none font-semibold">
                            <option value="free" class="bg-slate-800" @selected(old('plan', $tenant->plan) == 'free')>Free Tier</option>
                            <option value="pro" class="bg-slate-800 text-sky-400" @selected(old('plan', $tenant->plan) == 'pro')>Pro Tier</option>
                            <option value="enterprise" class="bg-slate-800 text-purple-400" @selected(old('plan', $tenant->plan) == 'enterprise')>Enterprise Tier</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Instance Status <span class="text-rose-400">*</span></label>
                    <div class="relative">
                        <select name="status" required class="w-full glass-input rounded-xl px-4 py-3 appearance-none font-semibold">
                            <option value="pending" class="bg-slate-800 text-amber-400" @selected(old('status', $tenant->status) == 'pending')>Pending Setup</option>
                            <option value="active" class="bg-slate-800 text-emerald-400" @selected(old('status', $tenant->status) == 'active')>Active / Live</option>
                            <option value="suspended" class="bg-slate-800 text-rose-400" @selected(old('status', $tenant->status) == 'suspended')>Suspended</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Max Students <span class="text-rose-400">*</span></label>
                    <input type="number" name="max_students" value="{{ old('max_students', $tenant->max_students) }}" required min="1"
                           class="w-full glass-input rounded-xl px-4 py-3 font-mono">
                    @error('max_students')<p class="text-rose-400 text-xs mt-2 font-semibold">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Max Teachers <span class="text-rose-400">*</span></label>
                    <input type="number" name="max_teachers" value="{{ old('max_teachers', $tenant->max_teachers) }}" required min="1"
                           class="w-full glass-input rounded-xl px-4 py-3 font-mono">
                    @error('max_teachers')<p class="text-rose-400 text-xs mt-2 font-semibold">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="pt-6 border-t border-white/10 flex justify-between gap-3">
                <div class="text-xs text-slate-500 flex flex-col justify-center">
                    <span>Created: {{ $tenant->created_at->format('M j, Y H:i') }}</span>
                    <span>Last updated: {{ $tenant->updated_at->format('M j, Y H:i') }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('superadmin.tenants.index') }}" class="px-6 py-3 rounded-xl font-bold text-slate-300 hover:text-white hover:bg-white/5 transition-colors">Cancel</a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-bold rounded-xl transition-all shadow-lg shadow-emerald-500/25 flex items-center gap-2">
                        <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
