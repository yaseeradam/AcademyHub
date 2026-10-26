@props([
    'title' => null,
    'description' => null,
    'keywords' => null,
    'image' => null,
    'type' => 'website',
    'robots' => 'index, follow',
    'canonical' => null,
    'schemaType' => 'SoftwareApplication',
])

@php
    $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

    $schoolName = config('academyhub.school_name');
    if (!$schoolName && $tenant) {
        $schoolName = $tenant->name;
    }
    $schoolName = $schoolName ?: config('app.name', 'AcademyHub');

    $titleSuffix = config('academyhub.seo_title_suffix', 'School Management System & Portal');

    if (!empty($title)) {
        $fullTitle = "{$title} | {$schoolName} - {$titleSuffix}";
    } else {
        $fullTitle = "{$schoolName} - {$titleSuffix}";
    }

    $metaDescription = $description 
        ?: config('academyhub.seo_meta_description', 'AcademyHub is a next-generation school management system with CBT exams, dual-shift attendance, automated report card generator, and fee billing.');

    $metaKeywords = $keywords 
        ?: config('academyhub.seo_keywords', 'school management system, school portal, online CBT exams, student attendance tracker, school report card generator, school fees billing, Nigeria school software');

    $canonicalUrl = $canonical ?: url()->current();

    // Determine social preview image (1200x630 or school logo)
    $socialImage = null;
    if (!empty($image)) {
        $socialImage = str_starts_with($image, 'http') ? $image : asset($image);
    } elseif ($tenant && !empty($tenant->logo)) {
        $socialImage = asset('uploads/' . str_replace('\\', '/', $tenant->logo));
    } elseif (config('academyhub.school_logo')) {
        $socialImage = asset('uploads/' . str_replace('\\', '/', config('academyhub.school_logo')));
    } else {
        $defaultImg = config('academyhub.seo_og_image', 'full.png');
        $socialImage = asset($defaultImg);
    }

    $baseUrl = rtrim(config('app.url', url('/')), '/');
    $isIndexable = !str_contains($robots, 'noindex');

    // Build Schema.org Graph for indexable pages
    $schemaJson = null;
    if ($isIndexable) {
        $schema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'SoftwareApplication',
                    '@id' => $canonicalUrl . '#software',
                    'name' => $schoolName,
                    'applicationCategory' => 'EducationalApplication',
                    'operatingSystem' => 'All modern web browsers, Android, iOS, Windows, macOS',
                    'description' => $metaDescription,
                    'url' => $canonicalUrl,
                    'image' => $socialImage,
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => '0',
                        'priceCurrency' => 'NGN',
                    ],
                    'featureList' => [
                        'Online Computer-Based Testing (CBT)',
                        'Dual-Shift Student & Staff Attendance Tracking (Western & Islamic)',
                        'Automated WAEC-Standard Report Card & Broadsheet Compilation',
                        'School Fees Billing & Online Paystack Integration',
                        'Automated Parent & Teacher WhatsApp Notifications',
                        'Biometric Attendance Machine Integration',
                    ],
                ],
                [
                    '@type' => 'EducationalOrganization',
                    '@id' => $baseUrl . '#organization',
                    'name' => $schoolName,
                    'url' => $baseUrl,
                    'logo' => $socialImage,
                    'description' => $metaDescription,
                ],
            ],
        ];

        $schemaJson = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
@endphp

{{-- Standard Primary SEO Metadata --}}
<title>{{ $fullTitle }}</title>
<meta name="title" content="{{ $fullTitle }}" />
<meta name="description" content="{{ $metaDescription }}" />
<meta name="keywords" content="{{ $metaKeywords }}" />
<meta name="robots" content="{{ $robots }}" />
<meta name="googlebot" content="{{ $robots }}" />
<meta name="bingbot" content="{{ $robots }}" />
<link rel="canonical" href="{{ $canonicalUrl }}" />

{{-- Geotargeting & Regional Metadata --}}
<meta name="geo.region" content="NG" />
<meta name="geo.placename" content="Nigeria" />

{{-- Open Graph Protocol (Facebook, WhatsApp, LinkedIn, Discord) --}}
<meta property="og:site_name" content="{{ $schoolName }}" />
<meta property="og:type" content="{{ $type }}" />
<meta property="og:url" content="{{ $canonicalUrl }}" />
<meta property="og:title" content="{{ $fullTitle }}" />
<meta property="og:description" content="{{ $metaDescription }}" />
<meta property="og:image" content="{{ $socialImage }}" />
<meta property="og:image:secure_url" content="{{ $socialImage }}" />
<meta property="og:image:alt" content="{{ $schoolName }} Logo" />
<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}" />

{{-- Twitter / X Cards --}}
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $fullTitle }}" />
<meta name="twitter:description" content="{{ $metaDescription }}" />
<meta name="twitter:image" content="{{ $socialImage }}" />
<meta name="twitter:image:alt" content="{{ $schoolName }} Logo" />

{{-- Schema.org JSON-LD Structured Data --}}
@if($schemaJson)
<script type="application/ld+json">
{!! $schemaJson !!}
</script>
@endif
