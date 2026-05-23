<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('[Request] ' . $request->method() . ' ' . $request->fullUrl(), [
            'ip'   => $request->ip(),
            'body' => $request->except(['password']),
        ]);

        $response = $next($request);

        Log::info('[Response] Status: ' . $response->getStatusCode() . ' → ' . $request->fullUrl());

        return $response;
    }
}