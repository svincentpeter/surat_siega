<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateLastActivity
{
    public function handle($request, Closure $next)
{
    try {
        if (Auth::check()) {
            DB::table('pengguna')
                ->where('id', Auth::id())
                ->update(['last_activity' => now()]);
        }
    } catch (\Throwable $e) {
        \Log::warning('UpdateLastActivity: gagal memperbarui last_activity — ' . $e->getMessage());
        // jangan hentikan request, lanjutkan saja
    }

    return $next($request);
}

}
