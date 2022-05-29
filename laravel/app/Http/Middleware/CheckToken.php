<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Response;
use Illuminate\Support\Facades\Auth;

class CheckToken extends Response {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
      $token = $request->header('dashboardtoken');

      if(!$token){
        $this->append(['errorLogin' => true]);
        $this->toast('error', 'Token inválido');
        return $this->response(true);
      }

      $user = User::where('token_dashboard', $token)->first();
      if(!$user){
        $this->append(['errorLogin' => true]);
        $this->toast('error', 'Token inválido');
        return $this->response(true);
      }

      Auth::loginUsingId($user->id);

      return $next($request);
    }
}
