<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'storeName' => 'ATELIER',
            'store_name' => 'ATELIER',
            'announcement_bar_left' => '🔥 EXCLUSIVE OFFERS — SAME-DAY CAIRO DISPATCH — CASH ON DELIVERY AVAILABLE',
            'homeHeroTitle' => 'CURATED HOME DECORATION',
            'homeHeroSubtitle' => 'Elevate your living space with affordable, high-rated aesthetic accents, ambient lighting, and handcrafted decor pieces.',
            'homepage_banner_title' => 'SPECIAL OFFERS — UP TO 40% OFF',
            'homepage_banner_subtitle' => 'MODERN ACCENTS & AMBIANCE FOR EVERY ROOM',
            'homepage_banner_image' => '/imges/decor/dec-lgt-crt-02.jpg',
            'homepage_banner_height' => 'medium',
            'homepage_banner_position' => 'center center',
            'homepage_banner_fit' => 'cover',
            'homepage_banner_overlay' => 'medium',
            'homepage_banner_link' => '/collections/all',
            'storefront_lang' => 'en',
            'currency' => 'EGP',
            'low_stock_threshold' => '5',
            'admin_google_emails' => 'monoahsec@gmail.com',
            'social_instagram' => 'https://www.instagram.com/atelier_store404?stkn=MTYybHFvbXNjazhnOA%3D%3D&utm_source=qr',
            'social_facebook' => 'https://www.facebook.com/share/1CWwWyeEQU/?mibextid=wwXIfr',
            'social_whatsapp' => '201000000000',
            'social_whatsapp_msg' => 'مرحباً، أود الاستفسار عن منتجات الديكور من Atelier',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
