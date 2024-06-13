<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->is('api/*') && !$request->expectsJson()) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}
