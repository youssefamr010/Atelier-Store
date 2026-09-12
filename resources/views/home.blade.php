@extends('layouts.app')

@section('title', ($settings['storeName'] ?? 'ATELIER') . ' — Precision Crafted Luxury Accessories & Bespoke EDC')

@section('meta_description', $settings['homeHeroSubtitle'] ?? 'Handcrafted full-grain Italian leather, aerospace titanium hardware, and minimalist RFID architecture.')

@section('content')
    <!-- 0. Top Admin-Controlled Promotional Banner (Smart Adaptive) -->
    @include('partials.promo-banner')

    <!-- 0.5 Instant Quick Category Navigation Strip -->
    @include('partials.categories')

    <!-- 1. Product Scroll Showcase -->
    @include('partials.showcase')

    <!-- 1.5 Recently Viewed Strip -->
    @include('partials.recently-viewed')

    <!-- 1.75 Brand Heritage & Editorial Story -->
    @include('partials.our-story')

    <!-- 2. Craftsmanship & Heritage Guarantee Banner -->
    @include('partials.banner')
@endsection
