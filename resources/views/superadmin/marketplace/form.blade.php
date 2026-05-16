@extends('layouts.superadmin')

@section('header_title', $component->exists ? 'Edit Component' : 'Add New Component')
@section('header_subtitle', 'Manage marketplace component details.')

@section('content')
<div class="max-w-3xl">
    <div style="margin-bottom:20px;">
        <a href="{{ route('superadmin.marketplace.index') }}" class="sa-btn sa-btn-ghost">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to List
        </a>
    </div>

    <form action="{{ $component->exists ? route('superadmin.marketplace.update', $component) : route('superadmin.marketplace.store') }}" method="POST">
        @csrf
        @if($component->exists)
            @method('PUT')
        @endif

        <div class="sa-panel" style="margin-bottom:20px;">
            <div class="sa-panel-header">
                <span class="sa-panel-title">Component Details</span>
            </div>
            <div style="padding:24px;">
                <div class="sa-grid-2" style="margin-bottom:20px;">
                    <div>
                        <label for="name" class="sa-form-label">Name <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $component->name) }}" required class="sa-form-input">
                        @error('name')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label for="slug" class="sa-form-label">Slug <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $component->slug) }}" required class="sa-form-input" placeholder="e.g. cbt">
                        @error('slug')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label for="route_name" class="sa-form-label">Route Name</label>
                    <input type="text" name="route_name" id="route_name" value="{{ old('route_name', $component->route_name) }}" class="sa-form-input" placeholder="e.g. cbt.index">
                    <div style="font-size:12px; color:var(--sa-muted); margin-top:4px;">
                        The named Laravel route schools will be navigated to after installing this plugin (e.g. <code>cbt.index</code>). Leave blank if no dedicated page exists.
                    </div>
                    @error('route_name')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>

                <div class="sa-grid-2" style="margin-bottom:20px;">
                    <div>
                        <label for="icon" class="sa-form-label">Icon (Emoji or SVG)</label>
                        <input type="text" name="icon" id="icon" value="{{ old('icon', $component->icon) }}" class="sa-form-input" placeholder="e.g. 📚 or <svg>...</svg>">
                        @error('icon')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="sa-form-label">Pricing Type</label>
                        <div style="display:flex; gap:16px; align-items:center; height:40px;">
                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                                <input type="radio" name="pricing_type" value="free" {{ old('price', $component->price) == 0 ? 'checked' : '' }} onchange="togglePrice(false)">
                                <span style="font-size:14px; font-weight:600; color:var(--sa-text);">Free</span>
                            </label>
                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                                <input type="radio" name="pricing_type" value="paid" {{ old('price', $component->price) > 0 ? 'checked' : '' }} onchange="togglePrice(true)">
                                <span style="font-size:14px; font-weight:600; color:var(--sa-text);">Paid</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="price_wrapper" style="margin-bottom:20px; {{ old('price', $component->price) > 0 ? '' : 'display:none;' }}">
                    <label for="price" class="sa-form-label">Price <span style="color:#ef4444;">*</span></label>
                    <input type="number" step="0.01" name="price" id="price" value="{{ old('price', $component->price) }}" class="sa-form-input">
                    @error('price')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:20px;">
                    <label for="description" class="sa-form-label">Description</label>
                    <textarea id="description" name="description" rows="3" class="sa-form-input" style="resize:vertical;">{{ old('description', $component->description) }}</textarea>
                    @error('description')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $component->exists ? $component->is_active : true) ? 'checked' : '' }}>
                        <div>
                            <span style="font-size:14px; font-weight:600; color:var(--sa-text);">Active</span>
                            <div style="font-size:12px; color:var(--sa-muted);">Component is available in the marketplace.</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:12px;">
            <a href="{{ route('superadmin.marketplace.index') }}" class="sa-btn sa-btn-ghost">Cancel</a>
            <button type="submit" class="sa-btn sa-btn-primary">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Save Component
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function togglePrice(isPaid) {
        const wrapper = document.getElementById('price_wrapper');
        const input = document.getElementById('price');
        if (isPaid) {
            wrapper.style.display = 'block';
            input.setAttribute('required', 'required');
        } else {
            wrapper.style.display = 'none';
            input.removeAttribute('required');
            input.value = '0';
        }
    }
</script>
@endpush
@endsection
