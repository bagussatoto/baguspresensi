<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk menegakkan Segregation of Duties (SoD) & ISO 27001 pada akun Auditor.
 * Akun dengan peran Auditor dibatasi hak akses baca murni (Read-Only).
 * Seluruh percobaan mutasi data (POST, PUT, PATCH, DELETE) diblokir dengan 403 Forbidden.
 */
class EnforceAuditorReadOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && method_exists($user, 'isAuditor') && $user->isAuditor()) {
            // Selalu izinkan logout untuk akun auditor
            if ($request->is('logout', '*/logout') || $request->routeIs('logout', '*.logout')) {
                return $next($request);
            }

            $method = strtoupper($request->method());
            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'message' => 'Akses Ditolak (SoD): Akun Auditor dibatasi hak akses baca murni (Read-Only). Mutasi data dilarang.',
                    ], 403);
                }

                abort(403, 'Akses Ditolak (SoD): Akun Auditor dibatasi hak akses baca murni (Read-Only). Mutasi data dilarang demi kepatuhan ISO 27001.');
            }
        }

        return $next($request);
    }
}
