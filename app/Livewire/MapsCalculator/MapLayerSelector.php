<?php

namespace App\Livewire\MapsCalculator;

use Livewire\Component;

class MapLayerSelector extends Component
{
    public string $selectedLayer = 'pnoa';
    public bool $showDropdown = false;

    // Sin listener propio: setLayer() ya dispara 'mapLayerChanged' para el
    // componente del mapa; escucharlo aquí hacía que se llamara a sí mismo en
    // bucle.

    public function mount(string $defaultLayer = 'pnoa'): void
    {
        $this->selectedLayer = $defaultLayer;
    }

    public function setLayer(string $layer): void
    {
        $this->selectedLayer = $layer;
        $this->dispatch('mapLayerChanged', layer: $layer);
    }

    public function toggleDropdown(): void
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function getLayerLabel(string $layer): string
    {
        return match ($layer) {
            'satellite' => __('mapscalculator.satellite'),
            'hybrid' => __('mapscalculator.hybrid'),
            'roadmap' => __('mapscalculator.roadmap'),
            'terrain' => __('mapscalculator.terrain'),
            'pnoa' => __('mapscalculator.pnoa'),
            default => ucfirst($layer),
        };
    }

    public function render()
    {
        return view('livewire.mapscalculator.map-layer-selector');
    }
}