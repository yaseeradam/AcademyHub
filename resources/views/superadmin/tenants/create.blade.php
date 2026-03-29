@extends('layouts.superadmin')

@section('header_title', 'Create New School')
@section('header_subtitle', 'Provision a new school instance in the system')

@section('header_actions')
    <a href="{{ route('superadmin.tenants.index') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-sm font-bold rounded-xl transition-colors border border-white/10">
        Back to List
    </a>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('superadmin.tenants.store') }}" method="POST" class="glass-card rounded-3xl p-8">
            @csrf
            
            <h2 class="text-xl font-black text-white border-b border-white/10 pb-4 mb-6">School Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">School Name <span class="text-rose-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required 
                           class="w-full glass-input rounded-xl px-4 py-3 placeholder-slate-500" 
                           placeholder="e.g. Greenwood High School">
                    @error('name')<p class="text-rose-400 text-xs mt-2 font-semibold">{{ $message }}</p>@enderror
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Custom Domain <span class="text-slate-500 font-normal">(Optional)</span></label>
                    <input type="text" name="domain" value="{{ old('domain') }}" 
                           class="w-full glass-input rounded-xl px-4 py-3 placeholder-slate-500" 
                           placeholder="e.g. portal.greenwood.edu">
                    @error('domain')<p class="text-rose-400 text-xs mt-2 font-semibold">{{ $message }}</p>@enderror
                    <p class="text-slate-500 text-xs mt-2">Leave blank to use an auto-generated subdomain slug.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Contact Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email') }}" 
                           class="w-full glass-input rounded-xl px-4 py-3 placeholder-slate-500" 
                           placeholder="admin@school.com">
                    @error('contact_email')<p class="text-rose-400 text-xs mt-2 font-semibold">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" 
                           class="w-full glass-input rounded-xl px-4 py-3 placeholder-slate-500" 
                           placeholder="+1 ...">
                    @error('contact_phone')<p class="text-rose-400 text-xs mt-2 font-semibold">{{ $message }}</p>@enderror
                </div>
            </div>

            <h2 class="text-xl font-black text-white border-b border-white/10 pb-4 mb-6">Subscription & Limits</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Pricing Plan <span class="text-rose-400">*</span></label>
                    <div class="relative">
                        <select name="plan" required class="w-full glass-input rounded-xl px-4 py-3 appearance-none font-semibold">
                            <option value="free" class="bg-slate-800" @selected(old('plan') == 'free')>Free Tier</option>
                            <option value="pro" class="bg-slate-800 text-sky-400" @selected(old('plan') == 'pro')>Pro Tier</option>
                            <option value="enterprise" class="bg-slate-800 text-purple-400" @selected(old('plan') == 'enterprise')>Enterprise Tier</option>
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
                            <option value="pending" class="bg-slate-800 text-amber-400" @selected(old('status') == 'pending')>Pending Setup</option>
                            <option value="active" class="bg-slate-800 text-emerald-400" @selected(old('status') == 'active')>Active / Live</option>
                            <option value="suspended" class="bg-slate-800 text-rose-400" @selected(old('status') == 'suspended')>Suspended</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Max Students <span class="text-rose-400">*</span></label>
                    <input type="number" name="max_students" value="{{ old('max_students', 500) }}" required min="1"
                           class="w-full glass-input rounded-xl px-4 py-3 font-mono">
                    @error('max_students')<p class="text-rose-400 text-xs mt-2 font-semibold">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Max Teachers <span class="text-rose-400">*</span></label>
                    <input type="number" name="max_teachers" value="{{ old('max_teachers', 50) }}" required min="1"
                           class="w-full glass-input rounded-xl px-4 py-3 font-mono">
                    @error('max_teachers')<p class="text-rose-400 text-xs mt-2 font-semibold">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="pt-6 border-t border-white/10 flex justify-end gap-3">
                <a href="{{ route('superadmin.tenants.index') }}" class="px-6 py-3 rounded-xl font-bold text-slate-300 hover:text-white hover:bg-white/5 transition-colors">Cancel</a>
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-sky-500 to-indigo-500 hover:from-sky-400 hover:to-indigo-400 text-white font-bold rounded-xl transition-all shadow-lg shadow-sky-500/25 flex items-center gap-2">
                    <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Create School Instance
                </button>
            </div>
        </form>
    </div>
@endsection
