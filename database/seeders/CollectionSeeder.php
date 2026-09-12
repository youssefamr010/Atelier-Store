<?php

namespace Database\Seeders;

use App\Models\Collection;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    public function run(): void
    {
        // If collections already exist, do NOT overwrite or re-insert deleted collections!
        if (Collection::exists()) {
            return;
        }

        $collections = [
            [
                'title' => 'Wallets & Cardholders',
                'slug' => 'wallets-cardholders',
                'description' => 'Precision-crafted Italian full-grain leather wallets and cardholders.',
                'image_url' => '',
                'sort_order' => 10,
            ],
            [
                'title' => 'Bags & Folios',
                'slug' => 'bags-folios',
                'description' => 'Luxury handcrafted leather bags and travel folios.',
                'image_url' => '',
                'sort_order' => 20,
            ],
            [
                'title' => 'EDC & Accessories',
                'slug' => 'edc-accessories',
                'description' => 'Aerospace titanium keychains, clips, and lifestyle accessories.',
                'image_url' => '',
                'sort_order' => 30,
            ],
        ];

        foreach ($collections as $data) {
            Collection::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'image_url' => $data['image_url'],
                    'status' => 'active',
                    'sort_order' => $data['sort_order'],
                ]
            );
        }
    }
}
