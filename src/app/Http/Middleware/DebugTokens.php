<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugTokens
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->method() === 'POST') {
            Log::info('POST debug', [
                'path' => $request->path(),
                'status' => $response->getStatusCode(),
                'session_id' => substr($request->session()->getId(), 0, 12),
                'session_token' => substr((string) $request->session()->token(), 0, 12),
                'provided_token' => substr((string) $request->input('_token'), 0, 12),
                'token_from' => $request->input('_token') ? 'input' : ($request->header('X-CSRF-TOKEN') ? 'header' : 'none'),
                'xsrf_cookie' => substr((string) $request->cookie('XSRF-TOKEN'), 0, 10),
                'cookies' => array_keys($request->cookies->all()),
            ]);
        }

        return $response;
    }
}