<?php

namespace Database\Seeders\Simulator;

use App\Models\Simulator\Finish;
use App\Models\Simulator\FinishVariant;
use App\Models\Simulator\RoomTemplate;
use Illuminate\Database\Seeder;

class SimulatorSeeder extends Seeder
{
    public function run(): void
    {
        // --- Room Templates ---
        $rooms = [
            ['name' => 'Cocina', 'slug' => 'cocina', 'default_area_m2' => 15.00, 'meshes' => ['floor' => 'floor', 'counter' => 'counter'],
             'photo_url' => '/simulator-rooms/COCINA.jpg'],
            ['name' => 'Cocina 2', 'slug' => 'cocina-2', 'default_area_m2' => 18.00, 'meshes' => ['floor' => 'floor', 'counter' => 'counter'],
             'photo_url' => '/simulator-rooms/COCINA 2.jpg'],
            ['name' => 'Sala de estar', 'slug' => 'sala-de-estar', 'default_area_m2' => 35.00, 'meshes' => ['floor' => 'floor'],
             'photo_url' => '/simulator-rooms/SALA DE ESTAR.jpg'],
            ['name' => 'Comedor', 'slug' => 'comedor', 'default_area_m2' => 25.00, 'meshes' => ['floor' => 'floor'],
             'photo_url' => '/simulator-rooms/comedor.jpg'],
            ['name' => 'Piscina', 'slug' => 'piscina', 'default_area_m2' => 45.00, 'meshes' => ['floor' => 'floor'],
             'photo_url' => '/simulator-rooms/PISCINA.jpg'],
            ['name' => 'Entrada', 'slug' => 'entrada', 'default_area_m2' => 12.00, 'meshes' => ['floor' => 'floor'],
             'photo_url' => '/simulator-rooms/entrada.jpg'],
            ['name' => 'Habitación', 'slug' => 'habitacion', 'default_area_m2' => 20.00, 'meshes' => ['floor' => 'floor'],
             'photo_url' => '/simulator-rooms/HABITACION.jpg'],
            ['name' => 'Terraza', 'slug' => 'terraza', 'default_area_m2' => 30.00, 'meshes' => ['floor' => 'floor'],
             'photo_url' => '/simulator-rooms/TERRAZA.jpg'],
            ['name' => 'Garaje', 'slug' => 'garaje', 'default_area_m2' => 25.00, 'meshes' => ['floor' => 'floor'],
             'photo_url' => '/simulator-rooms/garaje.jpg'],
            ['name' => 'Baño', 'slug' => 'bano', 'default_area_m2' => 8.00, 'meshes' => ['floor' => 'floor'],
             'photo_url' => '/simulator-rooms/BAÑO.jpg'],
            ['name' => 'Oficina', 'slug' => 'oficina', 'default_area_m2' => 40.00, 'meshes' => ['floor' => 'floor', 'counter' => 'counter'],
             'photo_url' => '/simulator-rooms/OFICINA.jpg'],
            ['name' => 'Patio', 'slug' => 'patio', 'default_area_m2' => 20.00, 'meshes' => ['floor' => 'floor'],
             'photo_url' => '/simulator-rooms/patio.jpg'],
            ['name' => 'Sótano', 'slug' => 'sotano', 'default_area_m2' => 30.00, 'meshes' => ['floor' => 'floor'],
             'photo_url' => '/simulator-rooms/sotano.jpg'],
            ['name' => 'Área Comercial', 'slug' => 'area-comercial', 'default_area_m2' => 50.00, 'meshes' => ['floor' => 'floor', 'counter' => 'counter'],
             'photo_url' => '/simulator-rooms/area-comercial.jpg'],
        ];

        foreach ($rooms as $room) {
            RoomTemplate::create([
                'name' => $room['name'],
                'slug' => $room['slug'],
                'model_glb_path' => null,
                'photo_url' => $room['photo_url'] ?? null,
                'configurable_meshes' => $room['meshes'],
                'default_area_m2' => $room['default_area_m2'],
                'is_active' => true,
            ]);
        }

        // --- Finishes ---
        $chips = Finish::create(['name' => 'Resina con chips de granito', 'type' => 'chips', 'price_per_m2' => 49.90, 'currency' => 'EUR', 'is_active' => true, 'sort_order' => 1]);
        $solid = Finish::create(['name' => 'Resina sólida epoxi', 'type' => 'solid', 'price_per_m2' => 39.90, 'currency' => 'EUR', 'is_active' => true, 'sort_order' => 2]);
        $metal = Finish::create(['name' => 'Resina metálica', 'type' => 'metallic', 'price_per_m2' => 59.90, 'currency' => 'EUR', 'is_active' => true, 'sort_order' => 3]);

        // --- Variants for Chips ---
        $chipVariants = [
            ['name' => 'Gris Urbano', 'hex' => '#7B7D7D', 'density' => 40],
            ['name' => 'Blanco Nieve', 'hex' => '#F5F5F5', 'density' => 30],
            ['name' => 'Negro Absoluto', 'hex' => '#1C1C1C', 'density' => 50],
            ['name' => 'Terracota', 'hex' => '#C0392B', 'density' => 35],
            ['name' => 'Azul Piedra', 'hex' => '#5D6D7E', 'density' => 45],
            ['name' => 'Verde Oliva', 'hex' => '#6B7B3A', 'density' => 38],
        ];

        foreach ($chipVariants as $i => $v) {
            FinishVariant::create([
                'simulator_finish_id' => $chips->id,
                'name' => $v['name'],
                'base_color_hex' => $v['hex'],
                'chip_density' => $v['density'],
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }

        // --- Variants for Solid ---
        $solidVariants = [
            ['name' => 'Blanco Puro', 'hex' => '#FFFFFF'],
            ['name' => 'Gris Claro', 'hex' => '#BDC3C7'],
            ['name' => 'Gris Oscuro', 'hex' => '#5D6D7E'],
            ['name' => 'Negro', 'hex' => '#1C1C1C'],
            ['name' => 'Crema', 'hex' => '#FDEBD0'],
            ['name' => 'Beige', 'hex' => '#D5C4A1'],
        ];

        foreach ($solidVariants as $i => $v) {
            FinishVariant::create([
                'simulator_finish_id' => $solid->id,
                'name' => $v['name'],
                'base_color_hex' => $v['hex'],
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }

        // --- Variants for Metallic ---
        $metalVariants = [
            ['name' => 'Plata', 'hex' => '#C0C0C0'],
            ['name' => 'Oro', 'hex' => '#D4AF37'],
            ['name' => 'Cobre', 'hex' => '#B87333'],
            ['name' => 'Hierro', 'hex' => '#4A4A4A'],
            ['name' => 'Bronce', 'hex' => '#CD7F32'],
            ['name' => 'Titanio', 'hex' => '#878681'],
        ];

        foreach ($metalVariants as $i => $v) {
            FinishVariant::create([
                'simulator_finish_id' => $metal->id,
                'name' => $v['name'],
                'base_color_hex' => $v['hex'],
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }
    }
}
