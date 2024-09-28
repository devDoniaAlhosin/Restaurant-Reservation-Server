<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */

    // Handle on session of laravel
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : response('Invalid' , 400);
    }

    // Handle Json request

    public function handle($request, Closure $next, ...$guards)
    {
        if($jwt = $request->cookie('token')){
             $request->headers->set('Authorization', 'Bearer ' . $jwt);
        }
        $this->authenticate($request, $guards);

        return $next($request);
    }



}
