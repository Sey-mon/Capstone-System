<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add cache headers for better performance
        if ($request->isMethod('GET') && !$request->ajax()) {
            $response->headers->set('Cache-Control', 'public, max-age=300'); // 5 minutes
            $response->headers->set('Vary', 'Accept-Encoding');
            
            // Add ETag for better caching
            $etag = md5($response->getContent());
            $response->headers->set('ETag', '"' . $etag . '"');
            
            // Check if client has cached version
            if ($request->header('If-None-Match') === '"' . $etag . '"') {
                return response('', 304)->withHeaders($response->headers->all());
            }
        }

        return $response;
    }
}
