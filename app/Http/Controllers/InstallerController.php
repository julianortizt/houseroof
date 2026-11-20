<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InstallerController extends Controller
{
    /**
     * Steps of the wizard, used by the progress indicator.
     */
    protected array $steps = [
        'welcome'  => 'Requisitos',
        'database' => 'Base de datos',
        'site'     => 'Sitio',
        'confirm'  => 'Confirmar',
        'complete' => 'Finalizado',
    ];

    // ============================================================
    // STEP 1 — Welcome & requirements
    // ============================================================

    public function welcome(): View
    {
        return view('installer.welcome', [
            'requirements' => $this->checkRequirements(),
            'steps'        => $this->steps,
            'current'      => 'welcome',
        ]);
    }

    // ============================================================
    // STEP 2 — Database configuration
    // ============================================================

    public function database(): View
    {
        $config = DB::connection('mysql')->getConfig(null) ?? [];

        $old = session('install.db', []);

        $values = [
            'db_host'     => $old['db_host'] ?? ($config['host'] ?? '127.0.0.1'),
            'db_port'     => $old['db_port'] ?? ($config['port'] ?? '3306'),
            'db_name'     => $old['db_name'] ?? '',
            'db_user'     => $old['db_user'] ?? '',
            'db_password' => $old['db_password'] ?? '',
        ];

        return view('installer.database', [
            'values'  => $values,
            'steps'   => $this->steps,
            'current' => 'database',
        ]);
    }

    public function postDatabase(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'db_host'     => 'required|string',
            'db_port'     => 'required|numeric',
            'db_name'     => 'required|string',
            'db_user'     => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        // Test the connection using an in-memory DB config.
        $connected = $this->testDatabaseConnection($data);

        if (!$connected['ok']) {
            return redirect()
                ->route('install.database')
                ->withInput()
                ->with('db_error', $connected['message']);
        }

        session(['install.db' => $data]);

        return redirect()->route('install.site');
    }

    // ============================================================
    // STEP 3 — Site configuration
    // ============================================================

    public function site(): View
    {
        $old = session('install.site', []);

        $values = [
            'app_name'      => $old['app_name'] ?? 'House Roof',
            'app_url'       => $old['app_url'] ?? rtrim((string) url('/'), '/'),
            'admin_email'   => $old['admin_email'] ?? '',
            'admin_password'=> $old['admin_password'] ?? '',
            'mail_host'     => $old['mail_host'] ?? 'smtp.gmail.com',
            'mail_port'     => $old['mail_port'] ?? '587',
            'mail_user'     => $old['mail_user'] ?? '',
            'mail_password' => $old['mail_password'] ?? '',
            'mail_from'     => $old['mail_from'] ?? '',
            'mail_encryption' => $old['mail_encryption'] ?? 'tls',
        ];

        return view('installer.site', [
            'values'  => $values,
            'steps'   => $this->steps,
            'current' => 'site',
        ]);
    }

    public function postSite(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'app_name'        => 'required|string|max:255',
            'app_url'         => 'required|url',
            'admin_email'     => 'required|email',
            'admin_password'  => 'required|string|min:8',
            'mail_host'       => 'required|string',
            'mail_port'       => 'required|numeric',
            'mail_user'       => 'required|string',
            'mail_password'   => 'nullable|string',
            'mail_from'       => 'nullable|email',
            'mail_encryption' => 'required|in:tls,ssl,null',
        ]);

        if (empty($data['mail_from'])) {
            $data['mail_from'] = $data['admin_email'];
        }

        session(['install.site' => $data]);

        return redirect()->route('install.confirm');
    }

    // ============================================================
    // STEP 4 — Confirmation
    // ============================================================

    public function confirm()
    {
        if (!session('install.db') || !session('install.site')) {
            return redirect()->route('install.welcome');
        }

        return view('installer.confirm', [
            'db'      => session('install.db'),
            'site'    => session('install.site'),
            'steps'   => $this->steps,
            'current' => 'confirm',
        ]);
    }

    // ============================================================
    // STEP 5 — Run the installation
    // ============================================================

    public function run(Request $request): RedirectResponse
    {
        if (!session('install.db') || !session('install.site')) {
            return redirect()->route('install.welcome');
        }

        $db   = session('install.db');
        $site = session('install.site');

        $result = $this->performInstall($db, $site);

        if (!$result['success']) {
            return redirect()
                ->route('install.confirm')
                ->with('install_error', $result['message']);
        }

        return redirect()->route('install.complete');
    }

    // ============================================================
    // STEP 6 — Completion
    // ============================================================

    public function complete()
    {
        if (!file_exists(base_path('.installed'))) {
            return redirect()->route('install.welcome');
        }

        $site = session('install.site', []);

        session()->forget(['install.db', 'install.site']);

        return view('installer.complete', [
            'app_url' => $site['app_url'] ?? url('/'),
            'steps'   => $this->steps,
            'current' => 'complete',
        ]);
    }

    // ============================================================
    // Core installation logic
    // ============================================================

    protected function performInstall(array $db, array $site): array
    {
        try {
            set_time_limit(300);

            // Make sure no stale config cache interferes with what we write.
            @Artisan::call('config:clear');
            @Artisan::call('route:clear');
            @Artisan::call('view:clear');

            $key = $this->generateKey();

            // Build the .env contents and write it.
            $env = $this->buildEnvContents($db, $site, $key);
            file_put_contents(base_path('.env'), $env);

            // Push the same values into the in-memory config so the artisan
            // commands executed below (same process) use them.
            $this->applyConfig($db, $site, $key);

            // Run migrations.
            DB::purge('mysql');
            Artisan::call('migrate', ['--force' => true]);

            // Seed safe initial data.
            Artisan::call('db:seed', ['--class' => InstallSeeder::class, '--force' => true]);

            // Create the administrator account from the form.
            $this->createAdminUser($site);

            // Storage link (best effort).
            try {
                Artisan::call('storage:link');
            } catch (\Throwable $e) {
                // Some shared hosts block symlinks; not fatal.
            }

            // Production caches.
            //
            // IMPORTANT: config:cache re-reads the config files using the
            // *boot-time* environment, not the .env we just wrote in this same
            // request. So we run the cache commands in a fresh subprocess that
            // boots with the new .env. If shell execution is unavailable, we
            // leave the app uncached (it still works by reading .env live).
            $this->cacheInSubprocess();

            // Lock the installer.
            file_put_contents(base_path('.installed'), date('c'));

            return ['success' => true];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function createAdminUser(array $site): void
    {
        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Throwable $e) {
            // registrar may not be booted yet; ignore
        }

        $existing = User::where('email', $site['admin_email'])->first();

        if ($existing) {
            $existing->update([
                'password' => bcrypt($site['admin_password']),
                'name'     => $site['app_name'] ?? $existing->name,
            ]);
            $existing->assignRole('super_admin');

            return;
        }

        $user = User::create([
            'name'     => 'Administrador',
            'email'    => $site['admin_email'],
            'password' => bcrypt($site['admin_password']),
        ]);

        $user->assignRole('super_admin');
    }

    // ============================================================
    // Helpers
    // ============================================================

    protected function testDatabaseConnection(array $db): array
    {
        try {
            config([
                'database.connections.mysql.host'     => $db['db_host'],
                'database.connections.mysql.port'     => $db['db_port'],
                'database.connections.mysql.database' => $db['db_name'],
                'database.connections.mysql.username' => $db['db_user'],
                'database.connections.mysql.password' => $db['db_password'] ?? '',
            ]);

            DB::purge('mysql');
            DB::connection('mysql')->getPdo();

            return ['ok' => true, 'message' => ''];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    protected function generateKey(): string
    {
        return 'base64:'.base64_encode(random_bytes(32));
    }

    /**
     * Run cache commands in a separate PHP process so they boot with the
     * freshly written .env (config:cache re-reads files, not runtime config).
     * Best effort: silently skipped if shell execution is disabled.
     */
    protected function cacheInSubprocess(): void
    {
        if (!function_exists('exec')) {
            return;
        }

        $php = $this->detectPhpBinary();

        $commands = [
            'config:cache',
            'route:cache',
            'view:cache',
        ];

        foreach ($commands as $command) {
            @exec(escapeshellarg($php).' artisan '.$command.' 2>&1', $output, $code);
        }
    }

    /**
     * Find a PHP binary that can run artisan (web CGI binary may differ from CLI).
     */
    protected function detectPhpBinary(): string
    {
        $candidates = array_filter(['php', PHP_BINARY, '/usr/bin/php', '/usr/local/bin/php']);

        foreach ($candidates as $candidate) {
            if (!$candidate) {
                continue;
            }

            @exec(escapeshellarg($candidate).' -v 2>&1', $output, $code);

            if ($code === 0) {
                return $candidate;
            }
        }

        return PHP_BINARY ?: 'php';
    }

    protected function applyConfig(array $db, array $site, string $key): void
    {
        $secure = str_starts_with((string) $site['app_url'], 'https');

        config([
            'app.name'      => $site['app_name'],
            'app.env'       => 'production',
            'app.debug'     => false,
            'app.url'       => $site['app_url'],
            'app.key'       => $key,
            'app.locale'    => 'en',
            'app.fallback_locale' => 'en',

            'database.connections.mysql.host'     => $db['db_host'],
            'database.connections.mysql.port'     => $db['db_port'],
            'database.connections.mysql.database' => $db['db_name'],
            'database.connections.mysql.username' => $db['db_user'],
            'database.connections.mysql.password' => $db['db_password'] ?? '',

            'mail.default'                   => 'smtp',
            'mail.mailers.smtp.host'         => $site['mail_host'],
            'mail.mailers.smtp.port'         => $site['mail_port'],
            'mail.mailers.smtp.username'     => $site['mail_user'],
            'mail.mailers.smtp.password'     => $site['mail_password'] ?? '',
            'mail.mailers.smtp.encryption'   => $site['mail_encryption'] === 'null' ? null : $site['mail_encryption'],
            'mail.from.address'              => $site['mail_from'],
            'mail.from.name'                 => $site['app_name'],

            'session.secure'   => $secure,
            'session.encrypt'  => false,

            'filament.path'     => 'admin',
            'filesystem.disks.public.visibility' => 'public',
        ]);
    }

    protected function buildEnvContents(array $db, array $site, string $key): string
    {
        $secure = str_starts_with((string) $site['app_url'], 'https');
        $encryption = $site['mail_encryption'] === 'null' ? 'null' : $site['mail_encryption'];

        $lines = [
            'APP_NAME="'.$this->escapeEnv((string) $site['app_name']).'"',
            'APP_ENV=production',
            'APP_KEY='.$key,
            'APP_DEBUG=false',
            'APP_URL='.$site['app_url'],
            'APP_TIMEZONE=Europe/Madrid',
            'APP_LOCALE=en',
            'APP_FALLBACK_LOCALE=en',
            'APP_FAKER_LOCALE=es_ES',
            'APP_MAINTENANCE_DRIVER=file',
            '',
            'LOG_CHANNEL=stack',
            'LOG_STACK=single',
            'LOG_DEPRECATIONS_CHANNEL=null',
            'LOG_LEVEL=error',
            '',
            'DB_CONNECTION=mysql',
            'DB_HOST='.$db['db_host'],
            'DB_PORT='.$db['db_port'],
            'DB_DATABASE='.$db['db_name'],
            'DB_USERNAME='.$db['db_user'],
            'DB_PASSWORD="'.$this->escapeEnv((string) ($db['db_password'] ?? '')).'"',
            '',
            'SESSION_DRIVER=file',
            'SESSION_LIFETIME=120',
            'SESSION_ENCRYPT=false',
            'SESSION_PATH=/',
            'SESSION_DOMAIN=null',
            'SESSION_SECURE_COOKIE='.($secure ? 'true' : 'false'),
            'SESSION_HTTPONLY=true',
            '',
            'CACHE_STORE=file',
            'QUEUE_CONNECTION=sync',
            '',
            'FILESYSTEM_DISK=public',
            '',
            'MAIL_MAILER=smtp',
            'MAIL_HOST='.$site['mail_host'],
            'MAIL_PORT='.$site['mail_port'],
            'MAIL_USERNAME='.$site['mail_user'],
            'MAIL_PASSWORD="'.$this->escapeEnv((string) ($site['mail_password'] ?? '')).'"',
            'MAIL_ENCRYPTION='.$encryption,
            'MAIL_FROM_ADDRESS='.$site['mail_from'],
            'MAIL_FROM_NAME="'.$this->escapeEnv((string) $site['app_name']).'"',
            '',
            'FILAMENT_PATH=admin',
            '',
            'GROQ_API_KEY=',
        ];

        return implode("\n", $lines)."\n";
    }

    protected function escapeEnv(string $value): string
    {
        return str_replace('"', '\\"', $value);
    }

    protected function checkRequirements(): array
    {
        $phpOk = PHP_MAJOR_VERSION >= 8 && PHP_MINOR_VERSION >= 2;

        // Extensions that are mandatory for the app to boot and migrate.
        $required = [
            'bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'json', 'mbstring',
            'openssl', 'pdo', 'pdo_mysql', 'tokenizer', 'xml',
        ];

        // Recommended but non-blocking (used by Composer / imaging / localization).
        $recommended = ['exif', 'intl', 'gd', 'zip'];

        $extChecks = [];
        foreach (array_merge($required, $recommended) as $ext) {
            $extChecks[$ext] = extension_loaded($ext);
        }

        $writable = [
            'storage'        => is_writable(storage_path()),
            'bootstrap/cache'=> is_writable(base_path('bootstrap/cache')),
            'project root'   => is_writable(base_path()),
        ];

        $allOk = $phpOk
            && !in_array(false, array_map(fn ($e) => $extChecks[$e], $required), true)
            && !in_array(false, $writable, true);

        return [
            'php_version'  => PHP_VERSION,
            'php_ok'       => $phpOk,
            'extensions'   => $extChecks,
            'required'     => $required,
            'recommended'  => $recommended,
            'writable'     => $writable,
            'all_ok'       => $allOk,
        ];
    }
}
