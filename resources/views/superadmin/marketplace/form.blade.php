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

    @if($component->exists)
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:20px;">
            <div class="sa-panel" style="padding:16px; display:flex; align-items:center; gap:12px; border-radius:16px;">
                <div style="width:40px; height:40px; border-radius:10px; background:#7c3aed15; color:#7c3aed; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg style="width:20px; height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                    </svg>
                </div>
                <div>
                    <div style="font-size:11px; font-weight:700; color:var(--sa-muted); text-transform:uppercase; letter-spacing:0.05em;">Real active installs</div>
                    <div style="font-size:18px; font-weight:900; color:var(--sa-text); margin-top:2px;">{{ $component->real_installs }} <span style="font-size:12px; font-weight:500; color:var(--sa-muted);">{{ Str::plural('school', $component->real_installs) }}</span></div>
                </div>
            </div>

            <div class="sa-panel" style="padding:16px; display:flex; align-items:center; gap:12px; border-radius:16px;">
                <div style="width:40px; height:40px; border-radius:10px; background:#b4530915; color:#b45309; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg style="width:20px; height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.784.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
                <div>
                    <div style="font-size:11px; font-weight:700; color:var(--sa-muted); text-transform:uppercase; letter-spacing:0.05em;">Real rating score</div>
                    <div style="font-size:18px; font-weight:900; color:var(--sa-text); margin-top:2px;">
                        @if($component->real_rating_count > 0)
                            {{ number_format($component->real_rating_avg, 1) }} <span style="font-size:12px; font-weight:500; color:var(--sa-muted);">({{ $component->real_rating_count }} {{ Str::plural('review', $component->real_rating_count) }})</span>
                        @else
                            <span style="font-size:13px; font-weight:700; color:var(--sa-muted);">No reviews</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ $component->exists ? route('superadmin.marketplace.update', $component) : route('superadmin.marketplace.store') }}" method="POST" enctype="multipart/form-data">
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
                                <input type="radio" name="pricing_type" value="free" {{ (old('setup_fee', $component->setup_fee) == 0 && old('usage_fee_per_student', $component->usage_fee_per_student) == 0) ? 'checked' : '' }} onchange="togglePrice(false)">
                                <span style="font-size:14px; font-weight:600; color:var(--sa-text);">Free</span>
                            </label>
                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                                <input type="radio" name="pricing_type" value="paid" {{ (old('setup_fee', $component->setup_fee) > 0 || old('usage_fee_per_student', $component->usage_fee_per_student) > 0) ? 'checked' : '' }} onchange="togglePrice(true)">
                                <span style="font-size:14px; font-weight:600; color:var(--sa-text);">Paid</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div id="price_wrapper" style="margin-bottom:20px; {{ (old('setup_fee', $component->setup_fee) > 0 || old('usage_fee_per_student', $component->usage_fee_per_student) > 0) ? '' : 'display:none;' }}">
                    <div class="sa-grid-2">
                        <div>
                            <label for="setup_fee" class="sa-form-label">Setup / Install Fee <span style="color:#ef4444;">*</span></label>
                            <input type="number" step="0.01" name="setup_fee" id="setup_fee" value="{{ old('setup_fee', $component->setup_fee ?? 0) }}" class="sa-form-input">
                            @error('setup_fee')<div class="sa-form-error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="usage_fee_per_student" class="sa-form-label">Usage Fee per Student (per Term) <span style="color:#ef4444;">*</span></label>
                            <input type="number" step="0.01" name="usage_fee_per_student" id="usage_fee_per_student" value="{{ old('usage_fee_per_student', $component->usage_fee_per_student ?? 0) }}" class="sa-form-input">
                            @error('usage_fee_per_student')<div class="sa-form-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label for="description" class="sa-form-label">Description</label>
                    <textarea id="description" name="description" rows="3" class="sa-form-input" style="resize:vertical;">{{ old('description', $component->description) }}</textarea>
                    @error('description')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $component->exists ? $component->is_active : true) ? 'checked' : '' }}>
                        <div>
                            <span style="font-size:14px; font-weight:600; color:var(--sa-text);">Active</span>
                            <div style="font-size:12px; color:var(--sa-muted);">Component is available in the marketplace.</div>
                        </div>
                    </label>
                </div>

                {{-- Screenshots Management Panel --}}
                <div class="sa-section-title" style="margin-top:28px; margin-bottom:18px; padding-top:16px; border-top:1.5px solid var(--sa-border);">Screenshots Preview Settings</div>
                
                <div class="sa-grid-2" style="margin-bottom:20px;">
                    <div>
                        <label for="screenshot_count" class="sa-form-label">Screenshot Count (1 to 5)</label>
                        <input type="number" min="1" max="5" name="screenshot_count" id="screenshot_count" value="{{ old('screenshot_count', $component->screenshot_count ?? 3) }}" class="sa-form-input" onchange="updateScreenshotsList(this.value)">
                        @error('screenshot_count')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div id="screenshots_wrapper" style="margin-bottom:20px;">
                    <div style="display:grid; grid-template-columns:1fr; gap:16px;">
                        @php
                            $metadata = $component->screenshots_metadata;
                            if (is_string($metadata)) {
                                $metadata = json_decode($metadata, true) ?: [];
                            }
                            $metadata = is_array($metadata) ? $metadata : [];
                        @endphp
                        @for($i = 0; $i < 5; $i++)
                            @php
                                $item = $metadata[$i] ?? null;
                                $title = is_array($item) ? ($item['title'] ?? 'Screenshot ' . ($i + 1)) : (is_string($item) ? $item : 'Screenshot ' . ($i + 1));
                                $filename = is_array($item) ? ($item['filename'] ?? '') : '';
                            @endphp
                            <div class="screenshot-row" data-index="{{ $i }}" style="background:#f8fafc; border:1.5px solid var(--sa-border); padding:18px; border-radius:12px; display:{{ $i < old('screenshot_count', $component->screenshot_count ?? 3) ? 'block' : 'none' }}">
                                <div style="font-size:12.5px; font-weight:700; color:var(--sa-text); margin-bottom:12px;">Screenshot Slot {{ $i + 1 }}</div>
                                <div class="sa-grid-2" style="margin-bottom:12px;">
                                    <div>
                                        <label class="sa-form-label" style="font-size:11px;">Display Title</label>
                                        <input type="text" name="screenshots[{{ $i }}][title]" value="{{ old('screenshots.'.$i.'.title', $title) }}" class="sa-form-input" placeholder="e.g. Overview Page">
                                    </div>
                                    <div>
                                        <label class="sa-form-label" style="font-size:11px;">Asset URL or Filename (Optional)</label>
                                        <input type="text" name="screenshots[{{ $i }}][filename]" id="screenshot_filename_{{ $i }}" value="{{ old('screenshots.'.$i.'.filename', $filename) }}" class="sa-form-input" placeholder="Will auto-fill on upload">
                                    </div>
                                </div>
                                <div style="display:flex; align-items:center; gap:16px;">
                                    <div style="flex:1;">
                                        <label class="sa-form-label" style="font-size:11px;">Upload Image File</label>
                                        <div class="screenshot-upload-dropzone" id="dropzone_{{ $i }}" style="position:relative; border:2px dashed var(--sa-border); border-radius:10px; padding:14px; text-align:center; background:#ffffff; cursor:pointer; transition:all 0.2s ease; display:flex; align-items:center; justify-content:center; gap:8px;">
                                            <input type="file" name="screenshot_files[{{ $i }}]" id="file_input_{{ $i }}" accept="image/*" style="position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%;" onchange="previewLocalImage(this, {{ $i }})">
                                            <svg style="width:20px; height:20px; color:var(--sa-muted); flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                            <span class="upload-text" id="upload_text_{{ $i }}" style="font-size:12px; color:var(--sa-muted); font-weight:600; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:260px;">
                                                Choose or drag file here
                                            </span>
                                        </div>
                                    </div>

                                    @php
                                        $previewUrl = '';
                                        if (!empty($filename)) {
                                            if (filter_var($filename, FILTER_VALIDATE_URL)) {
                                                $previewUrl = $filename;
                                            } elseif (file_exists(public_path('images/' . $filename))) {
                                                $previewUrl = asset('images/' . $filename);
                                            } elseif (file_exists(public_path($filename))) {
                                                $previewUrl = asset($filename);
                                            }
                                        }
                                    @endphp

                                    <div style="width:72px; height:72px; border-radius:10px; border:2px solid var(--sa-border); background:#ffffff; overflow:hidden; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 2px 4px rgba(0,0,0,0.05); position:relative;">
                                        <img id="preview_img_{{ $i }}" src="{{ !empty($previewUrl) ? $previewUrl : '' }}" style="width:100%; height:100%; object-fit:cover; {{ empty($previewUrl) ? 'display:none;' : '' }}">
                                        <span id="preview_placeholder_{{ $i }}" style="font-size:10px; color:var(--sa-muted); font-weight:700; text-align:center; display:block; padding:4px; word-break:break-all; line-height:1; {{ !empty($previewUrl) ? 'display:none;' : '' }}">No Image</span>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
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
<style>
    .screenshot-upload-dropzone:hover, .screenshot-upload-dropzone.dragover {
        border-color: #4f46e5 !important;
        background-color: #f5f3ff !important;
        color: #4f46e5 !important;
    }
    .screenshot-upload-dropzone:hover svg, .screenshot-upload-dropzone.dragover svg {
        color: #4f46e5 !important;
    }
</style>
<script>
    function togglePrice(isPaid) {
        const wrapper = document.getElementById('price_wrapper');
        const setupInput = document.getElementById('setup_fee');
        const usageInput = document.getElementById('usage_fee_per_student');
        if (isPaid) {
            wrapper.style.display = 'block';
            setupInput.setAttribute('required', 'required');
            usageInput.setAttribute('required', 'required');
        } else {
            wrapper.style.display = 'none';
            setupInput.removeAttribute('required');
            usageInput.removeAttribute('required');
            setupInput.value = '0';
            usageInput.value = '0';
        }
    }

    function updateScreenshotsList(count) {
        const rows = document.querySelectorAll('.screenshot-row');
        rows.forEach(row => {
            const index = parseInt(row.getAttribute('data-index'));
            if (index < count) {
                row.style.display = 'block';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function previewLocalImage(input, index) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImg = document.getElementById('preview_img_' + index);
                const placeholder = document.getElementById('preview_placeholder_' + index);
                const filenameInput = document.getElementById('screenshot_filename_' + index);
                const uploadText = document.getElementById('upload_text_' + index);
                
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
                placeholder.style.display = 'none';
                
                uploadText.textContent = file.name;
                
                if (!filenameInput.value) {
                    filenameInput.value = file.name;
                }
            }
            reader.readAsDataURL(file);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const dropzones = document.querySelectorAll('.screenshot-upload-dropzone');
        dropzones.forEach(zone => {
            zone.addEventListener('dragover', function(e) {
                e.preventDefault();
                zone.classList.add('dragover');
            });
            
            zone.addEventListener('dragleave', function() {
                zone.classList.remove('dragover');
            });
            
            zone.addEventListener('drop', function() {
                zone.classList.remove('dragover');
            });
        });
    });
</script>
@endpush
@endsection
