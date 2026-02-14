<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

/**
 * Consente l'accesso alle route di installazione solo se l'app non è ancora
 * installata (APP_KEY vuoto). Durante l'installazione forza la sessione su file
 * perché il database potrebbe non essere ancora configurato.
 */
class EnsureNotInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = config('app.key');
        if (! empty($key)) {
            return redirect()->route('home');
        }

        Config::set('session.driver', 'file');

        return $next($request);
    }
}
