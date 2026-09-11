<?php

namespace Database\Seeders;

use App\Models\Collection;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    public function run(): void
    {
        $collections = [
            [
                'title' => 'Lighting & Ambiance',
                'slug' => 'lighting-ambiance',
                'description' => 'Ambient LED string lights, minimalist taper candle holders, glowing neon wall signs, and pure soy aromatherapy candles.',
                'image_url' => '/imges/decor/dec-lgt-crt-02.jpg',
                'sort_order' => 10,
            ],
            [
                'title' => 'Plants & Vases',
                'slug' => 'plants-greenery',
                'description' => 'Faux potted succulents, botanical frosted eucalyptus stems, Scandinavian hollow ceramic donut vases, and ribbed glass vessels.',
                'image_url' => '/imges/decor/dec-pln-dnt-08.jpg',
                'sort_order' => 20,
            ],
            [
                'title' => 'Cushions & Textiles',
                'slug' => 'cushions-textiles',
                'description' => 'Gold-accented luxury Dutch velvet cushion covers, boho tufted fringe pillows, handwoven macrame table runners, and sheer linen drapes.',
                'image_url' => '/imges/decor/dec-cus-vlv-10.jpg',
                'sort_order' => 30,
            ],
            [
                'title' => 'Shelves & Storage',
                'slug' => 'shelves-storage',
                'description' => 'Modern floating ledge wall shelves, natural woven cotton rope storage hampers, iridescent leaf trinket dishes, and bamboo organizers.',
                'image_url' => '/imges/decor/dec-shf-flt-14.jpg',
                'sort_order' => 40,
            ],
            [
                'title' => 'Wall Art & Decor',
                'slug' => 'wall-art-decor',
                'description' => 'Self-adhesive 3D faux brick wall panels, hand-knotted macramé wall tapestries, gallery collage photo frame sets, and feather dream catchers.',
                'image_url' => '/imges/decor/dec-wal-mac-18.jpg',
                'sort_order' => 50,
            ],
            [
                'title' => 'Figurines & Accents',
                'slug' => 'figurines-accents',
                'description' => 'Sunburst geometric metal round wall mirrors, faux designer coffee table book stacks, handcrafted Mediterranean wooden sailboats, and statement ceramic vases.',
                'image_url' => '/imges/decor/dec-fig-mir-23.jpg',
                'sort_order' => 60,
            ],
        ];

        foreach ($collections as $data) {
            Collection::updateOrCreate(
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

        $validSlugs = collect($collections)->pluck('slug')->toArray();
        Collection::whereNotIn('slug', $validSlugs)->delete();
    }
}
