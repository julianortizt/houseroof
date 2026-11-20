<?php

namespace App\Filament\Resources\Concerns;

use App\Models\User;
use App\Services\PlatformModules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * @property static string $requiredPlatformModule
 */
trait RequiresPlatformModule
{
    protected static function requiredPlatformModuleName(): ?string
    {
        try {
            return (new \ReflectionClass(static::class))->getStaticPropertyValue('requiredPlatformModule');
        } catch (\ReflectionException) {
            return null;
        }
    }

    protected static function modulePermissionName(): ?string
    {
        return match (static::requiredPlatformModuleName()) {
            'products' => 'manage products',
            'inventory' => 'manage inventory',
            'quotes' => 'manage quotes',
            'work_orders' => 'manage work orders',
            'invoicing' => 'manage invoicing',
            'accounting' => 'manage accounting',
            'payments' => 'manage payments',
            'simulator' => 'manage simulator',
            'administration' => 'manage administration',
            'customer_portal' => 'manage customer portal',
            default => null,
        };
    }

    protected static function currentUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }

    protected static function canUseModulePermission(?User $user, ?string $permission): bool
    {
        if (! $permission || ! $user) {
            return true;
        }

        $isAdministrativeRole = $user->hasRole(['super_admin', 'admin', 'manager', 'site_admin']);

        return $isAdministrativeRole || $user->can($permission);
    }

    public static function canAccess(): bool
    {
        $moduleEnabled = app(PlatformModules::class)->enabled(static::requiredPlatformModuleName());
        if (! $moduleEnabled) {
            return false;
        }

        $permission = static::modulePermissionName();
        $user = static::currentUser();
        if ($permission && ! static::canUseModulePermission($user, $permission)) {
            return false;
        }

        return parent::canAccess();
    }

    public static function canViewAny(): bool
    {
        $moduleEnabled = app(PlatformModules::class)->enabled(static::requiredPlatformModuleName());
        if (! $moduleEnabled) {
            return false;
        }

        $permission = static::modulePermissionName();
        $user = static::currentUser();
        if ($permission && ! static::canUseModulePermission($user, $permission)) {
            return false;
        }

        return parent::canViewAny();
    }

    public static function canCreate(): bool
    {
        $moduleEnabled = app(PlatformModules::class)->enabled(static::requiredPlatformModuleName());
        if (! $moduleEnabled) {
            return false;
        }

        $permission = static::modulePermissionName();
        $user = static::currentUser();
        if ($permission && ! static::canUseModulePermission($user, $permission)) {
            return false;
        }

        return parent::canCreate();
    }

    public static function canEdit(Model $record): bool
    {
        $moduleEnabled = app(PlatformModules::class)->enabled(static::requiredPlatformModuleName());
        if (! $moduleEnabled) {
            return false;
        }

        $permission = static::modulePermissionName();
        $user = static::currentUser();
        if ($permission && ! static::canUseModulePermission($user, $permission)) {
            return false;
        }

        return parent::canEdit($record);
    }

    public static function canDelete(Model $record): bool
    {
        $moduleEnabled = app(PlatformModules::class)->enabled(static::requiredPlatformModuleName());
        if (! $moduleEnabled) {
            return false;
        }

        $permission = static::modulePermissionName();
        $user = static::currentUser();
        if ($permission && ! static::canUseModulePermission($user, $permission)) {
            return false;
        }

        return parent::canDelete($record);
    }

    public static function shouldRegisterNavigation(): bool
    {
        $moduleEnabled = app(PlatformModules::class)->enabled(static::requiredPlatformModuleName());
        if (! $moduleEnabled) {
            return false;
        }

        $permission = static::modulePermissionName();
        $user = static::currentUser();
        if ($permission && ! static::canUseModulePermission($user, $permission)) {
            return false;
        }

        return parent::shouldRegisterNavigation();
    }
}
