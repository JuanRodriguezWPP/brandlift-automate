<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogStoreRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (str_contains($request->url(), 'api/brandlift')) {
            \Illuminate\Support\Facades\Log::info('INCOMING API REQUEST', [
                'url' => $request->url()
            ]);
        }

        $response = $next($request);

        if (str_contains($request->url(), 'api/brandlift')) {
            \Illuminate\Support\Facades\Log::info('OUTGOING API RESPONSE', [
                'url' => $request->url(),
                'status' => $response->status()
            ]);
        }

        return $response;
    }
}
