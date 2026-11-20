<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\Concerns\RequiresPlatformModule;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    use RequiresPlatformModule;

    protected static string $requiredPlatformModule = 'administration';

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getNavigationGroup(): ?string
    {

        return __('admin.Configuración');

    }

    public static function getNavigationLabel(): string
    {

        return __('admin.Usuarios');

    }

    public static function getModelLabel(): string
    {

        return __('admin.Usuario');

    }

    public static function getPluralModelLabel(): string
    {

        return __('admin.Usuarios');

    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Usuario')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.Nombre'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label(__('admin.Email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('password')
                            ->label(__('admin.Contraseña'))
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),

                        Forms\Components\Select::make('role_ids')
                            ->label(__('admin.Rol'))
                            ->options(fn (): array => static::availableRoles()->pluck('name', 'id')->all())
                            ->preload()
                            ->searchable()
                            ->multiple()
                            ->required()
                            ->default(function () {
                                $id = DB::table('roles')->where('name', 'site_admin')->value('id');

                                return $id ? [(int) $id] : [];
                            }),

                        // Rol dentro de MapsCalculator (users.mapscalculator_role).
                        // La columna es NOT NULL con default 'comercial', así que el
                        // select refleja las tres opciones reales: no existe un estado
                        // "sin rol" y dejar el campo vacío reventaría el guardado.
                        // Sin esto el rol sólo se podía asignar por SQL/tinker, y el
                        // scoping del módulo (isComercial / isTecnico) no era usable.
                        Forms\Components\Select::make('mapscalculator_role')
                            ->label(__('admin.RolMapsCalculator'))
                            ->options([
                                'admin' => 'Administrador',
                                'comercial' => 'Comercial',
                                'tecnico' => 'Técnico',
                            ])
                            ->default('comercial')
                            ->helperText(__('admin.AyudaRolMapsCalculator'))
                            ->required()
                            ->native(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.Nombre'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('admin.Email'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label(__('admin.Rol'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'manager' => 'warning',
                        'site_admin' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'super_admin' => 'Super Admin',
                        'manager' => 'Gerente',
                        'site_admin' => 'Admin Sitio',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('mapscalculator_role')
                    ->label(__('admin.RolMapsCalculator'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'admin' => 'Administrador',
                        'comercial' => 'Comercial',
                        'tecnico' => 'Técnico',
                        default => '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'admin' => 'success',
                        'comercial' => 'info',
                        'tecnico' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.Creado'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (User $record): bool => static::canEdit($record)),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (User $record): bool => static::canDelete($record)),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function availableRoles(): \Illuminate\Support\Collection
    {
        $roles = Role::query()->where('guard_name', 'web');

        if (! static::canAssignSuperAdmin()) {
            $roles->where('name', '!=', 'super_admin');
        }

        return $roles->orderBy('name')->get();
    }

    public static function validatedRoleIds(mixed $roleIds): array
    {
        if (! is_array($roleIds) || $roleIds === []) {
            throw ValidationException::withMessages([
                'data.role_ids' => 'Debe asignar al menos un rol.',
            ]);
        }

        $ids = [];
        foreach ($roleIds as $roleId) {
            $validatedId = filter_var($roleId, FILTER_VALIDATE_INT);
            if ($validatedId === false || $validatedId < 1) {
                throw ValidationException::withMessages([
                    'data.role_ids' => 'La selección de roles no es válida.',
                ]);
            }
            $ids[] = $validatedId;
        }
        $ids = array_values(array_unique($ids));

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('id', $ids)
            ->get();

        if ($roles->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'data.role_ids' => 'La selección de roles no es válida.',
            ]);
        }

        if (! static::canAssignSuperAdmin() && $roles->contains('name', 'super_admin')) {
            throw ValidationException::withMessages([
                'data.role_ids' => 'Solo el desarrollador puede asignar el rol de superadministrador.',
            ]);
        }

        return $ids;
    }

    public static function assertCanUseDeveloperEmail(string $email): void
    {
        $developerEmail = config('platform.developer_email');

        if (
            filled($developerEmail)
            && strcasecmp(trim($email), $developerEmail) === 0
            && ! static::isDeveloper()
        ) {
            throw ValidationException::withMessages([
                'data.email' => 'Solo el desarrollador puede crear o asignar su cuenta.',
            ]);
        }
    }

    public static function canEdit(Model $record): bool
    {
        if ($record->email === config('platform.developer_email') || $record->hasRole('super_admin')) {
            return static::isDeveloper();
        }

        return true;
    }

    public static function canDelete(Model $record): bool
    {
        if ($record->email === config('platform.developer_email') || Auth::id() === $record->getKey()) {
            return false;
        }

        if ($record instanceof User && $record->hasRole('super_admin')) {
            return static::isDeveloper() && User::role('super_admin')->count() > 1;
        }

        return true;
    }

    private static function canAssignSuperAdmin(): bool
    {
        return static::isDeveloper();
    }

    private static function isDeveloper(): bool
    {
        $developerEmail = config('platform.developer_email');

        return filled($developerEmail) && Auth::user()?->email === $developerEmail;
    }
}
