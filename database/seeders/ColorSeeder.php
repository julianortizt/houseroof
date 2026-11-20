<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            ['slug' => 'blanco-clasico', 'name_es' => 'Blanco Clásico', 'name_en' => 'Classic White', 'desc_es' => 'Blanco puro y luminoso, ideal para acabados modernos y minimalistas.', 'sort' => 1],
            ['slug' => 'blanco-especial', 'name_es' => 'Blanco Especial', 'name_en' => 'Special White', 'desc_es' => 'Blanco con tono cálido especial, perfecto para interiores.', 'sort' => 2],
            ['slug' => 'crema-marfil', 'name_es' => 'Crema Marfil', 'name_en' => 'Crema Marfil', 'desc_es' => 'Tono cálido tipo mármol crema. Muy popular en la costa mediterránea.', 'sort' => 3],
            ['slug' => 'cuarzo-espanol', 'name_es' => 'Cuarzo Español', 'name_en' => 'Spanish Quartz', 'desc_es' => 'Cuarzo natural con tonos beige y dorados. Hecho con piedra española.', 'sort' => 4],
            ['slug' => 'cuarzo-roble', 'name_es' => 'Cuarzo Roble', 'name_en' => 'Oak Quartz', 'desc_es' => 'Cuarzo con tonos roble cálidos. Perfecto para terrazas.', 'sort' => 5],
            ['slug' => 'gris-claro', 'name_es' => 'Gris Claro', 'name_en' => 'Light Grey', 'desc_es' => 'Gris claro elegante y versátil. Complementa cualquier diseño.', 'sort' => 6],
            ['slug' => 'gris-azulado', 'name_es' => 'Gris Azulado', 'name_en' => 'Blue Grey', 'desc_es' => 'Gris con tonalidad azulada, ideal para zonas modernas.', 'sort' => 7],
            ['slug' => 'gris-oscuro', 'name_es' => 'Gris Oscuro', 'name_en' => 'Dark Grey', 'desc_es' => 'Gris oscuro sofisticado para acabados contemporáneos.', 'sort' => 8],
            ['slug' => 'negro', 'name_es' => 'Negro', 'name_en' => 'Black', 'desc_es' => 'Negro puro para efectos dramáticos y de alto contraste.', 'sort' => 9],
            ['slug' => 'marron-caramelo', 'name_es' => 'Marrón Caramelo', 'name_en' => 'Caramel Brown', 'desc_es' => 'Marrón cálido tipo caramelo. Natural y acogedor.', 'sort' => 10],
            ['slug' => 'rojo-alicante', 'name_es' => 'Rojo Alicante', 'name_en' => 'Alicantine Red', 'desc_es' => 'Rojo vibrante inspirado en la tierra alicantina. Único y llamativo.', 'sort' => 11],
            ['slug' => 'rosa-valencia', 'name_es' => 'Rosa Valencia', 'name_en' => 'Valencia Pink', 'desc_es' => 'Rosa suave valenciano, delicado y original.', 'sort' => 12],
        ];

        foreach ($colors as $data) {
            $color = Color::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'is_active' => true,
                    'sort_order' => $data['sort'],
                ]
            );

            $color->setTranslation('name', 'es', $data['name_es']);
            $color->setTranslation('name', 'en', $data['name_en']);
            $color->setTranslation('description', 'es', $data['desc_es']);
            $color->setTranslation('description', 'en', '');
            $color->save();
        }
    }
}
