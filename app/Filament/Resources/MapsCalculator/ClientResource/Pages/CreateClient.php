<?php

namespace App\Filament\Resources\MapsCalculator\ClientResource\Pages;

use App\Filament\Resources\MapsCalculator\ClientResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
