<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Log422Responses
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->status() === 422) {
            \Illuminate\Support\Facades\Log::warning('422 Response Logged', [
                'url' => $request->url(),
                'response' => $response->getContent()
            ]);
        }

        return $response;
    }
}
