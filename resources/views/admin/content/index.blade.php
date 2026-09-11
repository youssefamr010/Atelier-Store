@extends('layouts.admin')

@section('title', 'Site Content & Branding Control')

@section('content')
<div class="max-w-5xl space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 border-b-2 border-black pb-4">
        <div>
            <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-500">VISUAL IDENTITY & CONTENT SYSTEM</span>
            <h1 class="text-3xl font-black uppercase tracking-tight text-black">Site Content & Branding</h1>
            <p class="text-xs text-gray-500 mt-0.5">Every change here goes live on the store immediately — no deployment needed.</p>
        </div>
        <a href="{{ route('home') }}" target="_blank" class="border border-black bg-white px-4 py-2 text-xs font-bold uppercase hover:bg-gray-100">
            ↗ Preview Storefront
        </a>
    </div>

    <!-- ─── 0. System Maintenance Mode Control ───────────────────────── -->
    <div class="bg-amber-50 border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-lg"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg></span>
                    <h2 class="text-base font-black uppercase tracking-tight text-black">Storefront Maintenance Mode</h2>
                </div>
                <p class="text-xs text-gray-700 mt-0.5">
                    When enabled, visitors are shown the "Scheduled Archive Maintenance" page. Logged-in administrators can still browse and test the store normally.
                </p>
            </div>
            <div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="maintenance_mode" value="1" {{ in_array((string)($settings['maintenance_mode'] ?? '0'), ['1', 'true', 'on'], true) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-black"></div>
                    <span class="ml-3 text-xs font-black uppercase text-black">Enable Maintenance</span>
                </label>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.content.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <!-- ─── 1. Store Identity ─────────────────────────────────────── -->
        <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
            <div class="border-b-2 border-black pb-2">
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-400">SECTION 1</span>
                <h2 class="text-lg font-black uppercase tracking-tight text-black">Store Identity & Branding</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Store Name *</label>
                    <input type="text" name="store_name" value="{{ $settings['store_name'] ?? $settings['storeName'] ?? 'ATELIER' }}" class="w-full border-2 border-black p-2.5 text-sm font-black uppercase tracking-widest focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Store Tagline</label>
                    <input type="text" name="store_tagline" value="{{ $settings['store_tagline'] ?? '' }}" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Support Email</label>
                    <input type="email" name="support_email" value="{{ $settings['support_email'] ?? '' }}" placeholder="concierge@yourstore.com" class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Support Phone</label>
                    <input type="text" name="support_phone" value="{{ $settings['support_phone'] ?? '' }}" placeholder="+20 100 000 0000" class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Store Logo</label>
                    @if(!empty($settings['store_logo']))
                        <img src="{{ url($settings['store_logo']) }}" alt="Current Store Logo" class="h-12 mb-2 object-contain border border-black p-1">
                    @endif
                    <input type="file" name="store_logo_file" accept="image/*" class="w-full border-2 border-black p-2 text-xs bg-white focus:outline-none">
                    <span class="text-[9px] text-gray-400">Transparent PNG or SVG recommended</span>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Browser Favicon</label>
                    @if(!empty($settings['store_favicon']))
                        <img src="{{ url($settings['store_favicon']) }}" alt="Favicon" class="h-8 w-8 mb-2 object-contain border border-black p-1">
                    @endif
                    <input type="file" name="store_favicon_file" accept="image/*" class="w-full border-2 border-black p-2 text-xs bg-white focus:outline-none">
                    <span class="text-[9px] text-gray-400">32×32 or 64×64 .ico/.png</span>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Free Shipping Threshold (EGP)</label>
                    <input type="number" name="free_shipping_threshold" value="{{ $settings['free_shipping_threshold'] ?? '2500' }}" class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none">
                </div>
            </div>

            <!-- Language Setting -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-black/10">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1"><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg> Storefront Language / لغة المتجر</label>
                    <select name="storefront_lang" class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none bg-white cursor-pointer">
                        <option value="en" {{ ($settings['storefront_lang'] ?? 'en') === 'en' ? 'selected' : '' }}>English (LTR)</option>
                        <option value="ar" {{ ($settings['storefront_lang'] ?? 'en') === 'ar' ? 'selected' : '' }}>العربية — Arabic (RTL) · Noto Naskh</option>
                    </select>
                    <p class="text-[9px] text-gray-400 mt-1">Switches the storefront font, direction (RTL/LTR), and layout to the selected language.</p>
                </div>
            </div>
        </div>

        <!-- ─── 2. Announcement Bar ─────────────────────────────────── -->
        <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
            <div class="border-b-2 border-black pb-2">
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-400">SECTION 2</span>
                <h2 class="text-lg font-black uppercase tracking-tight text-black">Announcement Bar</h2>
                <p class="text-[10px] text-gray-500">The scrolling banner at the very top of every page</p>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Left Message</label>
                <input type="text" name="announcement_bar_left" value="{{ $settings['announcement_bar_left'] ?? '' }}" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Center Message</label>
                <input type="text" name="announcement_bar_center" value="{{ $settings['announcement_bar_center'] ?? '' }}" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Right Message</label>
                <input type="text" name="announcement_bar_right" value="{{ $settings['announcement_bar_right'] ?? '' }}" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
            </div>
        </div>

        <!-- ─── 3. Hero Section ─────────────────────────────────────── -->
        <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
            <div class="border-b-2 border-black pb-2">
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-400">SECTION 3</span>
                <h2 class="text-lg font-black uppercase tracking-tight text-black">Homepage Hero Section</h2>
                <p class="text-[10px] text-gray-500">The large, prominent hero panel on the main homepage</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Badge Text (small tag above title)</label>
                    <input type="text" name="homeHeroBadge" value="{{ $settings['homeHeroBadge'] ?? '' }}" class="w-full border-2 border-black p-2.5 text-xs font-mono uppercase focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Price / Warranty Badge</label>
                    <input type="text" name="homeHeroPriceBadge" value="{{ $settings['homeHeroPriceBadge'] ?? '' }}" placeholder="From 540 EGP · 2-Year Warranty" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Hero Headline (Main Title)</label>
                <input type="text" name="homeHeroTitle" value="{{ $settings['homeHeroTitle'] ?? '' }}" class="w-full border-2 border-black p-2.5 text-sm font-black uppercase focus:outline-none">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Hero Subtitle / Description</label>
                <textarea name="homeHeroSubtitle" rows="2" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">{{ $settings['homeHeroSubtitle'] ?? '' }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Primary CTA Button Text</label>
                    <input type="text" name="homeHeroCtaText" value="{{ $settings['homeHeroCtaText'] ?? 'Explore Catalog' }}" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">CTA Button Link</label>
                    <input type="text" name="homeHeroCtaLink" value="{{ $settings['homeHeroCtaLink'] ?? '/collections/all' }}" class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none">
                </div>
            </div>
        </div>

        <!-- ─── 4. Promo Banner Section ─────────────────────────────── -->
        <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
            <div class="border-b-2 border-black pb-2">
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-400">SECTION 4</span>
                <h2 class="text-lg font-black uppercase tracking-tight text-black">Promotional Banner / Campaign Media</h2>
                <p class="text-[10px] text-gray-500">Use an optimized image or a muted looping video below the hero</p>
            </div>

            <!-- Current Banner Preview -->
            @if(!empty($settings['homepage_banner_image']))
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Current Banner Image</label>
                    <div class="relative w-full h-32 border-2 border-black overflow-hidden">
                        <img src="{{ str_starts_with($settings['homepage_banner_image'], 'http') ? $settings['homepage_banner_image'] : url($settings['homepage_banner_image']) }}" alt="Current Promo Banner" class="w-full h-full object-cover">
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Upload New Banner Image</label>
                <input type="file" name="homepage_banner_file" accept="image/jpeg,image/png,image/webp" class="w-full border-2 border-black p-2 text-xs bg-white focus:outline-none">
                <span class="text-[9px] text-gray-400">Recommended: 1800×700px WebP/JPEG, Max 8MB</span>
            </div>

            @if(!empty($settings['homepage_banner_video']))
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Current Banner Video</label>
                    <video controls preload="metadata" class="w-full h-32 border-2 border-black object-cover">
                        <source src="{{ str_starts_with($settings['homepage_banner_video'], 'http') ? $settings['homepage_banner_video'] : url($settings['homepage_banner_video']) }}">
                    </video>
                    <label class="mt-2 inline-flex items-center gap-2 text-xs font-bold cursor-pointer"><input type="checkbox" name="remove_homepage_banner_video" value="1"> Remove video and use the image</label>
                </div>
            @endif

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Upload Banner Video</label>
                <input type="file" name="homepage_banner_video_file" accept="video/mp4,video/webm,video/quicktime" class="w-full border-2 border-black p-2 text-xs bg-white focus:outline-none">
                <span class="text-[9px] text-gray-400">MP4 or WebM recommended, muted loop, 8–15 seconds, maximum 50MB. Video automatically uses the image as a fallback poster.</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Banner Headline</label>
                    <input type="text" name="homepage_banner_title" value="{{ $settings['homepage_banner_title'] ?? '' }}" class="w-full border-2 border-black p-2.5 text-xs font-black uppercase focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Banner CTA Link</label>
                    <input type="text" name="homepage_banner_link" value="{{ $settings['homepage_banner_link'] ?? '/collections/all' }}" class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Banner Subtitle</label>
                <input type="text" name="homepage_banner_subtitle" value="{{ $settings['homepage_banner_subtitle'] ?? '' }}" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Height (CSS)</label>
                    <input type="text" name="homepage_banner_height" value="{{ $settings['homepage_banner_height'] ?? '48vh' }}" placeholder="e.g. 48vh, 600px" class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Overlay Strength</label>
                    <select name="homepage_banner_overlay" class="w-full border-2 border-black p-2.5 text-xs bg-white focus:outline-none">
                        <option value="none" {{ ($settings['homepage_banner_overlay'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                        <option value="light" {{ ($settings['homepage_banner_overlay'] ?? '') === 'light' ? 'selected' : '' }}>Light</option>
                        <option value="medium" {{ ($settings['homepage_banner_overlay'] ?? 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="dark" {{ ($settings['homepage_banner_overlay'] ?? '') === 'dark' ? 'selected' : '' }}>Dark</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Image Fit</label>
                    <select name="homepage_banner_fit" class="w-full border-2 border-black p-2.5 text-xs bg-white focus:outline-none">
                        <option value="cover" {{ ($settings['homepage_banner_fit'] ?? 'cover') === 'cover' ? 'selected' : '' }}>Cover</option>
                        <option value="contain" {{ ($settings['homepage_banner_fit'] ?? '') === 'contain' ? 'selected' : '' }}>Contain</option>
                        <option value="fill" {{ ($settings['homepage_banner_fit'] ?? '') === 'fill' ? 'selected' : '' }}>Fill</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Zoom % (100 = normal)</label>
                    <input type="number" name="homepage_banner_zoom" value="{{ $settings['homepage_banner_zoom'] ?? '100' }}" min="80" max="200" class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none">
                </div>
            </div>
        </div>

        <!-- ─── 5. Section Titles ────────────────────────────────────── -->
        <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
            <div class="border-b-2 border-black pb-2">
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-400">SECTION 5</span>
                <h2 class="text-lg font-black uppercase tracking-tight text-black">Section Titles & Trust Bar</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Collections / Categories Section Title</label>
                    <input type="text" name="section_categories_title" value="{{ $settings['section_categories_title'] ?? 'Curated Collections' }}" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Categories Section Subtitle</label>
                    <input type="text" name="section_categories_sub" value="{{ $settings['section_categories_sub'] ?? '' }}" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Featured Products Section Title</label>
                    <input type="text" name="section_featured_title" value="{{ $settings['section_featured_title'] ?? 'Featured Products' }}" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Best seller product</label>
                    <select name="featured_bestseller_product_id" class="w-full border-2 border-black bg-white p-2.5 text-xs focus:outline-none">
                        <option value="">No best seller badge</option>
                        @foreach($products as $settingsProduct)
                            <option value="{{ $settingsProduct->id }}" @selected((string) ($settings['featured_bestseller_product_id'] ?? '') === (string) $settingsProduct->id)>{{ $settingsProduct->title }} · {{ $settingsProduct->sku }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[10px] text-gray-500">Only this item receives the Best seller badge and featured-home link.</p>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Guarantee / Trust Bar Title</label>
                    <input type="text" name="guarantee_title" value="{{ $settings['guarantee_title'] ?? 'The ATELIER Guarantee' }}" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Guarantee / Trust Bar Subtitle</label>
                <input type="text" name="guarantee_subtitle" value="{{ $settings['guarantee_subtitle'] ?? '' }}" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
            </div>
        </div>

        <!-- ─── 6. Footer & Social Media ──────────────────────────────────── -->
        <div class="bg-white border-2 border-black p-6 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] space-y-4">
            <div class="border-b-2 border-black pb-2">
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-400">SECTION 6</span>
                <h2 class="text-lg font-black uppercase tracking-tight text-black">Footer &amp; Social Media Links</h2>
                <p class="text-xs text-gray-500 mt-1">These links appear in the footer and inside the mobile menu. Leave blank to hide any icon.</p>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Footer Brand Tagline</label>
                <textarea name="footer_brand_tagline" rows="2" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">{{ $settings['footer_brand_tagline'] ?? '' }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Footer Copyright Line</label>
                    <input type="text" name="footer_copyright" value="{{ $settings['footer_copyright'] ?? '' }}" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">Shipping Note in Footer</label>
                    <input type="text" name="footer_shipping_note" value="{{ $settings['footer_shipping_note'] ?? '' }}" class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
                </div>
            </div>

            <!-- Social Media Links -->
            <div class="border-t border-black/10 pt-4">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <h3 class="text-sm font-black uppercase tracking-tight text-black">Social Media & Contact Channels</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <!-- Instagram -->
                    <div>
                        <label class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">
                            <svg class="w-3.5 h-3.5 fill-current text-pink-600" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                            <span>Instagram URL</span>
                        </label>
                        <input type="url" name="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}" placeholder="https://instagram.com/yourpage" class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none">
                    </div>

                    <!-- Facebook -->
                    <div>
                        <label class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">
                            <svg class="w-3.5 h-3.5 fill-current text-blue-600" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            <span>Facebook URL</span>
                        </label>
                        <input type="url" name="social_facebook" value="{{ $settings['social_facebook'] ?? '' }}" placeholder="https://facebook.com/yourpage" class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none">
                    </div>

                </div>

                <!-- WhatsApp Number (separate row) -->
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">
                            <svg class="w-3.5 h-3.5 fill-current text-emerald-600" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                            <span>WhatsApp Number (with country code)</span>
                        </label>
                        <input type="text" name="social_whatsapp" value="{{ $settings['social_whatsapp'] ?? '' }}" placeholder="201000000000 (no + or spaces)" class="w-full border-2 border-black p-2.5 text-xs font-mono focus:outline-none">
                        <p class="text-[10px] text-gray-500 mt-1">Example: 201012345678 — this shows as the VIP WhatsApp button everywhere.</p>
                    </div>
                    <div>
                        <label class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-700 mb-1">
                            <svg class="w-3.5 h-3.5 stroke-[2] text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span>WhatsApp Default Message</span>
                        </label>
                        <input type="text" name="social_whatsapp_msg" value="{{ $settings['social_whatsapp_msg'] ?? '' }}" placeholder="Hello! I have an inquiry about your products." class="w-full border-2 border-black p-2.5 text-xs focus:outline-none">
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="sticky bottom-4 bg-white border-2 border-black p-4 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] flex items-center justify-between gap-4">
            <p class="text-xs text-gray-600">
                <strong><svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg> Instant Publish:</strong> Changes go live on every page of the store the moment you save — no cache clearing required.
            </p>
            <button type="submit" class="bg-black text-white px-8 py-3.5 text-xs font-bold uppercase tracking-widest hover:bg-gray-800 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] whitespace-nowrap transition-transform active:translate-y-0.5">
                Save All Changes →
            </button>
        </div>

    </form>
</div>
@endsection
