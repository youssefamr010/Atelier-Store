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
            'announcement_bar_left' => 'COMPLIMENTARY 24H EXPRESS DELIVERY ACROSS EGYPT',
            'homeHeroTitle' => 'THE ABSOLUTE WALLET EXPERIENCE',
            'homeHeroSubtitle' => 'Handcrafted full-grain Italian leather, aerospace titanium hardware, and minimalist RFID architecture.',
            'homepage_banner_title' => '',
            'homepage_banner_subtitle' => '',
            'homepage_banner_image' => '',
            'homepage_banner_height' => 'auto',
            'homepage_banner_position' => 'center center',
            'homepage_banner_fit' => 'contain',
            'homepage_banner_overlay' => 'medium',
            'homepage_banner_link' => '',
            'storefront_lang' => 'en',
            'currency' => 'EGP',
            'low_stock_threshold' => '5',
            'social_instagram' => 'https://www.instagram.com/atelier_store404?stkn=MTYybHFvbXNjazhnOA%3D%3D&utm_source=qr',
            'social_facebook' => 'https://www.facebook.com/share/1CWwWyeEQU/?mibextid=wwXIfr',
            'social_whatsapp' => '201000000000',
            'social_whatsapp_msg' => 'مرحباً، أود الاستفسار عن منتجات Atelier',
        ];

        // Only insert settings if they do not exist yet; never overwrite existing admin settings!
        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
