<?php

namespace Database\Seeders;

use App\Enums\ProductLength;
use App\Models\SizePreset;
use App\Models\SizePresetChartRow;
use App\Models\SizePresetVariant;
use Illuminate\Database\Seeder;

class SizePresetSeeder extends Seeder
{
    public function run(): void
    {
        $preset = SizePreset::query()->updateOrCreate(
            ['slug' => 'hirurgichnyi-kostyum-standard'],
            [
                'name' => 'Хірургічний костюм — стандарт',
                'description' => 'Базова сітка розмірів XS–XL для хірургічних костюмів NOEMA.',
                'size_chart_intro' => 'Виміри в сантиметрах.',
                'length_guide' => null,
                'is_active' => true,
                'sort_order' => 10,
            ],
        );

        $chartRows = [
            ['size_label' => 'XS', 'bust' => '80–84', 'waist' => '60–64', 'hip' => '86–90', 'inseam' => '76'],
            ['size_label' => 'S', 'bust' => '84–88', 'waist' => '64–68', 'hip' => '90–94', 'inseam' => '77'],
            ['size_label' => 'M', 'bust' => '88–92', 'waist' => '68–72', 'hip' => '94–98', 'inseam' => '78'],
            ['size_label' => 'L', 'bust' => '92–96', 'waist' => '72–76', 'hip' => '98–102', 'inseam' => '79'],
            ['size_label' => 'XL', 'bust' => '96–100', 'waist' => '76–80', 'hip' => '102–106', 'inseam' => '80'],
        ];

        foreach ($chartRows as $index => $row) {
            SizePresetChartRow::query()->updateOrCreate(
                [
                    'size_preset_id' => $preset->id,
                    'size_label' => $row['size_label'],
                ],
                [...$row, 'sort_order' => ($index + 1) * 10],
            );
        }

        foreach (['XS', 'S', 'M', 'L', 'XL'] as $index => $size) {
            SizePresetVariant::query()->updateOrCreate(
                [
                    'size_preset_id' => $preset->id,
                    'size' => $size,
                    'length' => ProductLength::Regular->value,
                ],
                ['sort_order' => ($index + 1) * 10],
            );
        }
    }
}
