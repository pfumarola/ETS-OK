<?php

namespace App\Http\Middleware;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /** TTL cache URL firmato logo (secondi), come in PublicSiteController. */
    private const LOGO_SIGNED_URL_CACHE_TTL = 3000;

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $logoAttachment = Attachment::forSetting('logo')->first();
        $logoUrl = null;
        if ($logoAttachment) {
            $logoUrl = Cache::remember('public.logo.signed_url', self::LOGO_SIGNED_URL_CACHE_TTL, function () use ($logoAttachment) {
                return URL::temporarySignedRoute(
                    'public.logo.show',
                    now()->addMinutes(60),
                    ['attachment' => $logoAttachment->id]
                );
            });
        }
        $user = $request->user();
        $authMember = null;
        if ($user && $user->member) {
            $m = $user->member;
            $authMember = ['id' => $m->id, 'full_name' => $m->full_name];
        }

        return [
            ...parent::share($request),
            // Ziggy: route disponibili in JS; aggiornato a ogni richiesta così dopo login (navigazione Inertia) route('dashboard') funziona
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            // Token CSRF per form nativi (es. upload allegati)
            'csrf_token' => csrf_token(),
            // Ruoli utente per menu e permessi frontend (Jetstream gestisce auth.user)
            'userRoles' => $user ? $user->roles->pluck('name')->toArray() : [],
            // Socio collegato (per area self-service: menu "Il mio profilo", Modifica su Show)
            'authMember' => $authMember,
            // URL logo associazione (presigned, in cache; per header/layout e pagina login)
            'logo_url' => $logoUrl,
            // Flash message (redirect con with('flash', ['type' => ..., 'message' => ...]))
            'flash' => $request->session()->pull('flash'),
            // Versioni API disponibili (per documentazione Token API)
            'apiVersions' => config('api.versions', ['v1']),
            // Versione applicazione (semver) visibile in area riservata e pagine auth
            'appVersion' => config('app.version'),
        ];
    }
}
