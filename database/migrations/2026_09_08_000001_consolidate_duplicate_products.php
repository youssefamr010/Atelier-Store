<?php

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $pink = Product::where('slug', 'atelier-silicone-protection-case-rose-pink')->first();
        $blue = Product::where('slug', 'atelier-silicone-protection-case-midnight-blue')->first();

        if ($pink || $blue) {
            $unified = Product::updateOrCreate(
                ['slug' => 'atelier-silicone-protection-case'],
                [
                    'sku' => 'ATL-CSE-SIL-09',
                    'title' => 'ATELIER Liquid Silicone Shockproof Case',
                    'description' => 'Engineered with 2.6mm raised camera bezel and 1.2mm screen edge lip. Triple-layer drop protection with silky-soft liquid silicone exterior, microfiber inner lining, and fingerprint-resistant matte finish.',
                    'image_url' => $pink?->image_url ?: '/imges/Screenshot 2026-09-03 210306.png',
                    'cost_price_minor' => 12000,
                    'retail_price_minor' => 38000,
                    'currency' => 'EGP',
                    'inventory' => 155,
                    'status' => 'active',
                    'attributes_json' => [
                        'Material' => 'Medical-Grade Liquid Silicone',
                        'Bezel' => '2.6mm Raised Camera Lip + 1.2mm Screen Edge',
                        'Compatibility' => 'iPhone 15 & 16 Series',
                        'Protection' => 'Military Drop-Tested 10ft',
                    ],
                ]
            );

            // Link collection
            $caseCol = Collection::where('slug', 'cases-protection')->first();
            if ($caseCol) {
                $unified->collections()->syncWithoutDetaching([$caseCol->id]);
            }

            // Create Rose Pink variant
            $unified->variants()->updateOrCreate(
                ['sku' => 'ATL-CSE-SIL-PNK'],
                [
                    'title' => 'Rose Pink',
                    'attribute_name' => 'Color',
                    'cost_price_minor' => 12000,
                    'retail_price_minor' => 38000,
                    'inventory' => 80,
                    'image_url' => '/imges/Screenshot 2026-09-03 210306.png',
                    'attributes_json' => ['color_hex' => '#E8A3B7', 'color' => 'Rose Pink'],
                    'status' => 'active',
                ]
            );

            // Create Midnight Blue variant
            $unified->variants()->updateOrCreate(
                ['sku' => 'ATL-CSE-SIL-BLU'],
                [
                    'title' => 'Midnight Blue',
                    'attribute_name' => 'Color',
                    'cost_price_minor' => 12000,
                    'retail_price_minor' => 38000,
                    'inventory' => 75,
                    'image_url' => '/imges/Screenshot 2026-09-03 211134.png',
                    'attributes_json' => ['color_hex' => '#191970', 'color' => 'Midnight Blue'],
                    'status' => 'active',
                ]
            );

            // Archive old split products
            if ($pink) {
                $pink->update(['status' => 'archived']);
            }
            if ($blue) {
                $blue->update(['status' => 'archived']);
            }
        }
    }

    public function down(): void {}
};
