@php
    $cbtInstalled = $this->isCbtInstalled();
    $savingsLoanInstalled = $this->isSavingsLoanInstalled();
@endphp

<div class="space-y-6">
    <x-page-header title="Marketplace" subtitle="Discover and install premium modules and extensions." accent="more">
        <x-slot:actions>
            <a href="{{ route('more-features') }}" class="btn-outline">Back</a>
        </x-slot:actions>
    </x-page-header>

    @if ($errors->has('premium'))
        <div class="card-padded border border-orange-200 bg-orange-50/60 text-sm text-orange-900">
            {{ $errors->first('premium') }}
        </div>
    @endif

    <!-- Featured Products Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Featured Products</h2>
            <span class="text-sm text-gray-500">4 products available</span>
        </div>
        
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-2">
            <!-- WhatsApp Bot Product -->
            <a href="{{ route('marketplace.product', 'whatsapp-bot') }}" class="group block">
                <div class="card-padded hover:shadow-lg transition-all duration-200 border-2 border-transparent group-hover:border-green-200 group-hover:bg-green-50/30">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg">
                                <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-green-700 transition-colors">WhatsApp Bot</h3>
                                    <p class="text-sm text-gray-600 mt-1">Automated parent notifications & interactive bot</p>
                                </div>
                                <div class="flex items-center gap-1 text-amber-500">
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span class="text-sm font-semibold">4.8</span>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">FREE</span>
                                    <span class="text-xs text-gray-500">Communication</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500">1.2k downloads</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Student Dashboard Product -->
            <a href="{{ route('marketplace.product', 'student-dashboard') }}" class="group block">
                <div class="card-padded hover:shadow-lg transition-all duration-200 border-2 border-transparent group-hover:border-blue-200 group-hover:bg-blue-50/30">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-700 transition-colors">Student Dashboard</h3>
                                    <p class="text-sm text-gray-600 mt-1">Complete student portal with results & attendance</p>
                                </div>
                                <div class="flex items-center gap-1 text-amber-500">
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span class="text-sm font-semibold">4.6</span>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">FREE</span>
                                    <span class="text-xs text-gray-500">Education</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500">890 downloads</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- CBT Product -->
            <a href="{{ route('marketplace.product', 'cbt') }}" class="group block">
                <div class="card-padded hover:shadow-lg transition-all duration-200 border-2 border-transparent group-hover:border-purple-200 group-hover:bg-purple-50/30">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center shadow-lg">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-purple-700 transition-colors">CBT</h3>
                                    <p class="text-sm text-gray-600 mt-1">Computer-based testing system</p>
                                </div>
                                <div class="flex items-center gap-1 text-amber-500">
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span class="text-sm font-semibold">4.7</span>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">FREE</span>
                                    <span class="text-xs text-gray-500">Examination</span>
                                </div>
                                <div class="text-right">
                                    @if ($cbtInstalled)
                                        <x-status-badge variant="success">Installed</x-status-badge>
                                    @else
                                        <div class="text-xs text-gray-500">650 downloads</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Savings/Loan Product -->
            <a href="{{ route('marketplace.product', 'savings-loan') }}" class="group block">
                <div class="card-padded hover:shadow-lg transition-all duration-200 border-2 border-transparent group-hover:border-emerald-200 group-hover:bg-emerald-50/30">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-emerald-700 transition-colors">Savings / Loan</h3>
                                    <p class="text-sm text-gray-600 mt-1">Financial management module</p>
                                </div>
                                <div class="flex items-center gap-1 text-amber-500">
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span class="text-sm font-semibold">4.5</span>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">FREE</span>
                                    <span class="text-xs text-gray-500">Finance</span>
                                </div>
                                <div class="text-right">
                                    @if ($savingsLoanInstalled)
                                        <x-status-badge variant="success">Installed</x-status-badge>
                                    @else
                                        <div class="text-xs text-gray-500">420 downloads</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>