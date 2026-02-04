@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
    'url' => null,
])

@php
    $studio = app(\App\Services\TenantService::class)->current();
    $siteName = $studio?->name ?? config('app.name');

    $pageTitle = $title ? "{$title} | {$siteName}" : $siteName;
    $pageDescription = $description ?? ($studio?->settings['meta_description'] ?? "Welcome to {$siteName}");
    $pageUrl = $url ?? request()->url();
    $pageImage = $image ?? ($studio?->logo_path ? Storage::url($studio->logo_path) : null);
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDescription }}">

{{-- Open Graph Tags --}}
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $pageUrl }}">
<meta property="og:site_name" content="{{ $siteName }}">
@if($pageImage)
<meta property="og:image" content="{{ $pageImage }}">
@endif

{{-- Twitter Card Tags --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
@if($pageImage)
<meta name="twitter:image" content="{{ $pageImage }}">
@endif

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $pageUrl }}">
