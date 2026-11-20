<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Services\AuditLogger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    private array $roleIds = [];
    private array $previousRoleNames = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role_ids'] = $this->record->roles()->pluck('roles.id')->map(fn ($id): int => (int) $id)->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->previousRoleNames = $this->record->roles()->pluck('name')->all();
        UserResource::assertCanUseDeveloperEmail((string) ($data['email'] ?? ''));
        $this->roleIds = UserResource::validatedRoleIds($data['role_ids'] ?? null);
        unset($data['role_ids']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncRoles($this->roleIds);
        app(AuditLogger::class)->recordRoleChange(
            $this->record,
            $this->previousRoleNames,
            $this->record->roles()->pluck('name')->all(),
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => UserResource::canDelete($this->record)),
        ];
    }
}
