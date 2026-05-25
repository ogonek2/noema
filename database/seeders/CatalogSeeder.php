<?php

namespace Database\Seeders;

use App\Models\Catalog;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        Catalog::query()->updateOrCreate(
            ['slug' => 'hirurgichni-kostyumy'],
            [
                'name' => 'Хірургічні костюми',
                'description' => 'Преміальні хірургічні костюми NOEMA для довгих змін у операційній, стаціонарі та амбулаторії. Кожен колір — окремий товар з власною галереєю та розмірною сіткою.',
                'sort_order' => 10,
                'is_active' => true,
            ],
        );
    }
}
