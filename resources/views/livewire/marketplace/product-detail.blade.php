<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('marketplace') }}" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $productData['name'] }}</h1>
            <p class="text-sm text-gray-600">{{ $productData['category'] }}</p>
        </div>
    </div>

    <!-- Product Overview Card -->
    <div class="card-padded">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Product Icon -->
            <div class="flex-shrink-0">
                <div class="h-24 w-24 rounded-3xl bg-gradient-to-br 
                    @if($productData['color'] === 'green') from-green-500 to-emerald-600 
                    @elseif($productData['color'] === 'blue') from-blue-500 to-indigo-600
                    @elseif($productData['color'] === 'purple') from-purple-500 to-violet-600
                    @elseif($productData['color'] === 'emerald') from-emerald-500 to-teal-600 
                    @endif
                    flex items-center justify-center shadow-lg">
                    @if($productData['icon'] === 'whatsapp')
                        <svg class="h-12 w-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                        </svg>
                    @elseif($productData['icon'] === 'student')
                        <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        </svg>
                    @elseif($productData['icon'] === 'exam')
                        <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    @elseif($productData['icon'] === 'finance')
                        <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
            </div>

            <!-- Product Details -->
            <div class="flex-1">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $productData['name'] }}</h2>
                        <p class="text-gray-600 mt-1">{{ $productData['short_description'] }}</p>
                        
                        <!-- Rating & Downloads -->
                        <div class="flex items-center gap-4 mt-3">
                            <div class="flex items-center gap-1">
                                <div class="flex items-center gap-1 text-amber-500">
                                    @for($i = 0; $i < 5; $i++)
                                        <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="text-sm font-semibold text-gray-700">{{ $productData['rating'] }}</span>
                            </div>
                            <div class="text-sm text-gray-500">{{ $productData['downloads'] }} downloads</div>
                        </div>
                    </div>

                    <!-- Price & Install Button -->
                    <div class="flex flex-col items-end gap-3">
                        <div class="text-right">
                            <div class="text-2xl font-bold 
                                @if($productData['color'] === 'green') text-green-600 
                                @elseif($productData['color'] === 'blue') text-blue-600
                                @elseif($productData['color'] === 'purple') text-purple-600
                                @elseif($productData['color'] === 'emerald') text-emerald-600 
                                @endif">
                                {{ $productData['price'] }}
                            </div>
                            <div class="text-xs text-gray-500">{{ $productData['category'] }}</div>
                        </div>
                        <button class="btn-primary px-8 py-3">
                            Install Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Screenshots Section -->
    <div class="card-padded">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Screenshots</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($productData['screenshots'] as $screenshot)
                <div class="aspect-video bg-gray-100 rounded-xl flex items-center justify-center border-2 border-dashed border-gray-300">
                    <div class="text-center text-gray-500">
                        <svg class="h-12 w-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm">{{ $screenshot }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Description & Features -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Description -->
        <div class="lg:col-span-2 space-y-6">
            <div class="card-padded">
                <h3 class="text-lg font-bold text-gray-900 mb-4">About this product</h3>
                <p class="text-gray-700 leading-relaxed">{{ $productData['description'] }}</p>
            </div>

            <!-- Features -->
            <div class="card-padded">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Key Features</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($productData['features'] as $feature)
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 h-5 w-5 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="h-3 w-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-700">{{ $feature }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Requirements -->
            <div class="card-padded">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Requirements</h3>
                <ul class="space-y-2">
                    @foreach($productData['requirements'] as $requirement)
                        <li class="flex items-start gap-2 text-sm text-gray-700">
                            <div class="flex-shrink-0 h-1.5 w-1.5 rounded-full bg-gray-400 mt-2"></div>
                            {{ $requirement }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Benefits -->
            <div class="card-padded">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Benefits</h3>
                <ul class="space-y-3">
                    @foreach($productData['benefits'] as $benefit)
                        <li class="flex items-start gap-3">
                            <div class="flex-shrink-0 h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center mt-0.5">
                                <svg class="h-3 w-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-700">{{ $benefit }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Install Button -->
            <div class="card-padded bg-gray-50 border-2 border-blue-200">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600 mb-2">{{ $productData['price'] }}</div>
                    <button class="btn-primary w-full py-3">Install {{ $productData['name'] }}</button>
                    <p class="text-xs text-gray-500 mt-2">Compatible with MyAcademy v2.0+</p>
                </div>
            </div>
        </div>
    </div>
</div>