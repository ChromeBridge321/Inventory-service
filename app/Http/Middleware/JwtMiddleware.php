<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key as JWTKey;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Laravel\Prompts\Key;
use LDAP\Result;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request ->header('Authorization');
        if(!$token){
            return response()->json(['error'=>'Token not provided'], HttpResponse::HTTP_UNAUTHORIZED);
        }
        try {
           $token = str_replace('Bearer ', '', $token);
           $decoded = JWT::decode($token, new JWTKey(env('JWT_SECRET'),'HS256'));
           return $next($request);
        } catch (ExpiredException $e) {
           return response()->json(['error'=>'Token has expired'], HttpResponse::HTTP_UNAUTHORIZED);
        }  catch (Exception $e) {
            return response()->json(['error'=>'Invalid token: '.$e->getMessage()], HttpResponse::HTTP_UNAUTHORIZED);
        }

        
    }
}
