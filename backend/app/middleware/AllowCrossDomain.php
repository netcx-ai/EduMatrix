<?php
declare (strict_types = 1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

class AllowCrossDomain
{
    public function handle($request, Closure $next)
    {
        if (strtoupper($request->method()) == "OPTIONS") {
            $response = Response::create();
            $response->header([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With, token, Accept, Origin, Cache-Control, X-File-Name',
                'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
                'Access-Control-Max-Age' => '1728000',
                'Access-Control-Allow-Credentials' => 'true'
            ]);
            $response->code(204);
            return $response;
        }

        $response = $next($request);
        $response->header([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With, token, Accept, Origin, Cache-Control, X-File-Name',
            'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
            'Access-Control-Max-Age' => '1728000',
            'Access-Control-Allow-Credentials' => 'true'
        ]);

        return $response;
    }
}