<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAPIToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorized = false;
        // пока проверяем для GET-запросов
//        $token = $request->all()['token'];
        $token = $request->header('X-Token');
        if (!is_null($token)) {
            $authorized = User::checkAPIToken($token);
        }
        if ($authorized) {
            return $next($request);
        } else {
            return response(null, Response::HTTP_UNAUTHORIZED);
        }
    }
}
