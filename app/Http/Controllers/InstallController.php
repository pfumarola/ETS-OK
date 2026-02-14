<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstallController extends Controller
{
    /**
     * Mostra lo step corrente (database o utente admin).
     */
    public function show(Request $request)
    {
        if ($request->session()->has('install.db')) {
            return view('install.admin');
        }

        return view('install.database');
    }

    /**
     * Torna allo step 1 (database) cancellando i dati in sessione.
     */
    public function back(Request $request)
    {
        $request->session()->forget('install.db');

        return redirect()->route('install.show');
    }

    /**
     * Valida e salva la configurazione database in sessione; testa la connessione.
     */
    public function storeDatabase(Request $request)
    {
        $connection = $request->input('db_connection', 'mysql');

        $rules = [
            'db_connection' => 'required|in:mysql,sqlite',
        ];

        if ($connection === 'mysql') {
            $rules['db_host'] = 'required|string|max:255';
            $rules['db_port'] = 'nullable|string|max:10';
            $rules['db_database'] = 'required|string|max:255';
            $rules['db_username'] = 'required|string|max:255';
            $rules['db_password'] = 'nullable|string';
        } else {
            $rules['db_database_sqlite'] = 'nullable|string|max:500';
        }

        $validated = $request->validate($rules);

        $dbConfig = [
            'connection' => $validated['db_connection'],
            'host' => $validated['db_host'] ?? '127.0.0.1',
            'port' => $validated['db_port'] ?? ($connection === 'mysql' ? '3306' : null),
            'database' => $connection === 'sqlite'
                ? (trim($validated['db_database_sqlite'] ?? '') ?: database_path('database.sqlite'))
                : $validated['db_database'],
            'username' => $validated['db_username'] ?? '',
            'password' => $validated['db_password'] ?? '',
        ];

        if ($connection === 'sqlite') {
            $path = $dbConfig['database'];
            if ($path !== ':memory:') {
                $dir = dirname($path);
                if (! is_dir($dir)) {
                    if (! @mkdir($dir, 0755, true)) {
                        throw ValidationException::withMessages([
                            'db_database' => ['Impossibile creare la directory per il database SQLite.'],
                        ]);
                    }
                }
                if (! file_exists($path)) {
                    touch($path);
                }
            }
        }

        $this->testDatabaseConnection($dbConfig);

        $request->session()->put('install.db', $dbConfig);

        return redirect()->route('install.show');
    }

    /**
     * Completa l'installazione: scrive .env, migra, seed, redirect al login.
     */
    public function complete(Request $request)
    {
        $db = $request->session()->get('install.db');
        if (! $db) {
            return redirect()->route('install.show')->withErrors(['session' => 'Configurazione database non trovata. Ricomincia dallo step 1.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            $example = base_path('.env.example');
            if (file_exists($example)) {
                copy($example, $envPath);
            } else {
                return redirect()->back()->withErrors(['env' => 'File .env non trovato e .env.example assente.'])->withInput();
            }
        }

        $this->writeEnvDatabase($envPath, $db);
        $this->setRuntimeDatabaseConfig($db);
        Artisan::call('key:generate', ['--force' => true]);

        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['migrate' => 'Migrazioni fallite: '.$e->getMessage()])->withInput();
        }

        Config::set('install.admin', [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);
        try {
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\InstallSeeder', '--force' => true]);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['seed' => 'Seed fallito: '.$e->getMessage()])->withInput();
        }

        $request->session()->forget('install');

        return redirect()->route('login')->with('flash', [
            'type' => 'success',
            'message' => 'Installazione completata. Accedi con le credenziali inserite.',
        ]);
    }

    /**
     * Imposta la configurazione database a runtime così migrate/seed usano i valori appena scritti in .env.
     */
    private function setRuntimeDatabaseConfig(array $db): void
    {
        Config::set('database.default', $db['connection']);
        if ($db['connection'] === 'sqlite') {
            Config::set('database.connections.sqlite', [
                'driver' => 'sqlite',
                'url' => null,
                'database' => $db['database'],
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]);
        } else {
            Config::set('database.connections.mysql', [
                'driver' => 'mysql',
                'host' => $db['host'],
                'port' => $db['port'] ?? '3306',
                'database' => $db['database'],
                'username' => $db['username'],
                'password' => $db['password'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]);
        }
    }

    /**
     * Testa la connessione al database con la configurazione fornita.
     */
    private function testDatabaseConnection(array $db): void
    {
        $connection = $db['connection'];

        if ($connection === 'sqlite') {
            $config = [
                'driver' => 'sqlite',
                'database' => $db['database'],
                'prefix' => '',
            ];
        } else {
            $config = [
                'driver' => 'mysql',
                'host' => $db['host'],
                'port' => $db['port'] ?? '3306',
                'database' => $db['database'],
                'username' => $db['username'],
                'password' => $db['password'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ];
        }

        Config::set('database.connections.install_test', $config);

        try {
            DB::connection('install_test')->getPdo();
        } catch (\Throwable $e) {
            DB::purge('install_test');
            throw ValidationException::withMessages([
                'db_database' => ['Connessione al database non riuscita: '.$e->getMessage()],
            ]);
        }

        DB::purge('install_test');
    }

    /**
     * Aggiorna o inserisce le variabili DB nel file .env.
     */
    private function writeEnvDatabase(string $envPath, array $db): void
    {
        $content = file_get_contents($envPath);
        $vars = [
            'DB_CONNECTION' => $db['connection'],
        ];

        if ($db['connection'] === 'mysql') {
            $vars['DB_HOST'] = $db['host'];
            $vars['DB_PORT'] = $db['port'] ?? '3306';
            $vars['DB_DATABASE'] = $db['database'];
            $vars['DB_USERNAME'] = $db['username'];
            $vars['DB_PASSWORD'] = $this->envValue($db['password']);
        } else {
            $vars['DB_DATABASE'] = $db['database'];
        }

        foreach ($vars as $key => $value) {
            $content = $this->setEnvVariable($content, $key, $value);
        }

        file_put_contents($envPath, $content);
    }

    private function envValue(string $value): string
    {
        if (preg_match('/\s|#|$/', $value)) {
            return '"'.str_replace('"', '\\"', $value).'"';
        }

        return $value;
    }

    private function setEnvVariable(string $content, string $key, string $value): string
    {
        $line = $key.'='.$value;
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
        if (preg_match($pattern, $content)) {
            return preg_replace($pattern, $line, $content);
        }

        return $content."\n".$line."\n";
    }
}
