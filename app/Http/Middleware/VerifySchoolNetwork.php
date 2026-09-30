<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class VerifySchoolNetwork
{
    /**
     * Handle an incoming request.
     * Ensures request originates from verified school Wi-Fi / LAN subnets.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $enforce = config('attendance.enforce_wifi_restriction', false);

        if (! $enforce) {
            return $next($request);
        }

        $clientIp = $request->ip();
        $allowedSubnets = config('attendance.school_wifi_subnets', ['127.0.0.1', '::1']);

        if (! IpUtils::checkIp($clientIp, $allowedSubnets)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'OUTSIDE_SCHOOL_NETWORK',
                    'message' => 'Presensi ditolak. Anda harus terhubung ke jaringan Wi-Fi resmi sekolah.',
                    'client_ip' => $clientIp,
                ], 403);
            }

            return response()->view('errors.school_network_required', [
                'clientIp' => $clientIp,
            ], 403);
        }

        return $next($request);
    }
}
