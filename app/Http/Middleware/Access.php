<?php

namespace App\Http\Middleware;

use App\Http\Controllers\UserController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Access
{
    /**
     * kontrola zda je uzivatel prihlasen
     *
     */



    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = UserController::getLoggedUser();
        // dd($user["status"]);
        if ($user["status"] == "error") {
            return abort(404);
        } else {
            return $next($request);
        }
    }
}
