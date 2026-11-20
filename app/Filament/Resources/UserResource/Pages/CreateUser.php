<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    private array $roleIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        UserResource::assertCanUseDeveloperEmail((string) ($data['email'] ?? ''));
        $this->roleIds = UserResource::validatedRoleIds($data['role_ids'] ?? null);
        unset($data['role_ids']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncRoles($this->roleIds);
        app(AuditLogger::class)->recordRoleChange($this->record, [], $this->record->roles()->pluck('name')->all());
    }
}
