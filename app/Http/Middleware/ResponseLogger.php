<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ResponseLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = hrtime(true);
        $response = $next($request);
        $durationInMs = (hrtime(true) - $start) / 1_000_000;

        Log::info(sprintf(
            '%s %s %s %d %.2fms',
            $request->method(),
            $request->getRequestUri(),
            $response->headers->get('Content-Type', 'unknown'),
            $response->getStatusCode(),
            $durationInMs
        ));

        return $response;
    }
}
