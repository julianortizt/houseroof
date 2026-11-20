<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlatformModules
{
    private const MODULES = [
        'products' => [
            'label' => 'Product catalog and ecommerce',
            'description' => 'Public catalog and product-management screens. Online checkout requires the Payments module and Stripe configuration.',
            'status' => 'Catalog available; checkout requires setup',
            'default' => true,
            'dependencies' => [],
        ],
        'inventory' => [
            'label' => 'Inventory',
            'description' => 'Item catalogue, locations, auditable stock movements, and tracked material consumption from active work orders.',
            'status' => 'Partially available',
            'default' => true,
            'dependencies' => [],
        ],
        'quotes' => [
            'label' => 'Quotes and estimates',
            'description' => 'Controls the internal MapsCalculator quote workspace. Public quote requests remain available.',
            'status' => 'Partially available',
            'default' => true,
            'dependencies' => [],
        ],
        'work_orders' => [
            'label' => 'Work orders',
            'description' => 'Controls MapsCalculator work orders and their administrative screens.',
            'status' => 'Partially available',
            'default' => true,
            'dependencies' => ['quotes'],
        ],
        'invoicing' => [
            'label' => 'Invoicing',
            'description' => 'Only internal non-fiscal draft snapshots and preparatory business fiscal details are available. Fiscal issuance remains blocked pending legal review.',
            'status' => 'Drafts and configuration only',
            'default' => true,
            'dependencies' => ['quotes'],
        ],
        'accounting' => [
            'label' => 'Accounting',
            'description' => 'An internal generic double-entry journal and trial balance are available. The statutory Spanish chart, tax books, period close, and bank reconciliation are not implemented.',
            'status' => 'Internal journal only',
            'default' => true,
            'dependencies' => ['invoicing'],
        ],
        'payments' => [
            'label' => 'Online payments',
            'description' => 'Separate flags control product checkout and service quote deposits/final balances. Service payments also require invoicing, Stripe credentials, and fiscal review confirmation; administrator refunds are audited.',
            'status' => 'Configured but disabled by default',
            'default' => false,
            'dependencies' => ['quotes'],
        ],
        'simulator' => [
            'label' => '3D simulator',
            'description' => 'The simulator is restricted to authenticated administrators; its data-management screens are in the admin panel.',
            'status' => 'Partially available',
            'default' => true,
            'dependencies' => [],
        ],
        'administration' => [
            'label' => 'User administration',
            'description' => 'Controls user-management screens in the admin panel.',
            'status' => 'Partially available',
            'default' => true,
            'dependencies' => [],
        ],
        'customer_portal' => [
            'label' => 'Customer portal',
            'description' => 'Verified customer accounts for viewing their own quotes, work orders, and orders.',
            'status' => 'Partially available',
            'default' => true,
            'dependencies' => [],
        ],
    ];

    public function all(): array
    {
        $settings = Setting::safeGetMany(
            array_map(fn (string $module): string => "module_{$module}_enabled", array_keys(self::MODULES)),
        );

        return collect(self::MODULES)
            ->map(function (array $module, string $key) use ($settings): array {
                $module['key'] = $key;
                $module['enabled'] = (bool) ($settings["module_{$key}_enabled"] ?? $module['default']);

                return $module;
            })
            ->all();
    }

    public function enabled(string $module): bool
    {
        $definition = self::MODULES[$module] ?? null;

        if ($definition === null) {
            throw new \InvalidArgumentException("Unknown platform module [{$module}].");
        }

        return (bool) Setting::safeGet("module_{$module}_enabled", $definition['default']);
    }

    public function setEnabled(string $module, bool $enabled): void
    {
        if (! array_key_exists($module, self::MODULES)) {
            throw new \InvalidArgumentException("Unknown platform module [{$module}].");
        }

        $this->setEnabledModules([$module => $enabled]);
    }

    public function setEnabledModules(array $requested): void
    {
        $knownModules = array_keys(self::MODULES);
        $unknownModules = array_diff(array_keys($requested), $knownModules);

        if ($unknownModules !== []) {
            throw new \InvalidArgumentException('Unknown platform module [' . reset($unknownModules) . '].');
        }

        foreach ($requested as $module => $enabled) {
            if (! is_bool($enabled)) {
                throw ValidationException::withMessages([
                    "moduleSettings.{$module}" => 'Module availability must be a boolean value.',
                ]);
            }
        }

        DB::transaction(function () use ($requested): void {
            $current = collect($this->all())
                ->mapWithKeys(fn (array $definition, string $key): array => [$key => $definition['enabled']])
                ->all();
            $resultingState = array_replace($current, $requested);

            foreach (self::MODULES as $module => $definition) {
                if (! ($resultingState[$module] ?? false)) {
                    continue;
                }

                $missingDependencies = array_filter(
                    $definition['dependencies'],
                    fn (string $dependency): bool => ! ($resultingState[$dependency] ?? false),
                );

                if ($missingDependencies !== []) {
                    $dependencyLabels = array_map(
                        fn (string $dependency): string => self::MODULES[$dependency]['label'],
                        $missingDependencies,
                    );
                    $changedDependency = collect($missingDependencies)
                        ->first(fn (string $dependency): bool => array_key_exists($dependency, $requested));
                    $errorKey = $changedDependency ? "moduleSettings.{$changedDependency}" : "moduleSettings.{$module}";

                    throw ValidationException::withMessages([
                        $errorKey => "{$definition['label']} requires " . implode(', ', $dependencyLabels) . '.',
                    ]);
                }
            }

            $oldModules = [];
            $newModules = [];
            foreach ($resultingState as $module => $enabled) {
                if ($current[$module] !== $enabled) {
                    $oldModules[$module] = $current[$module];
                    $newModules[$module] = $enabled;
                }

                Setting::set("module_{$module}_enabled", $enabled ? '1' : '0', 'boolean', 'modules');
            }

            app(AuditLogger::class)->recordModuleChanges($oldModules, $newModules);
        });

        $this->forgetModuleCaches();
    }

    private function forgetModuleCaches(): void
    {
        foreach ([
            'home:featured_products',
            'home:categories',
            'nav:categories_with_products',
            'simulator.room_templates',
            'simulator.finishes',
            'simulator.quotes_count',
        ] as $cacheKey) {
            Cache::forget($cacheKey);
        }
    }
}
