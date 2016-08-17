<?php

namespace App\Http\Middleware;

use Closure;

class JsonMiddleware
{
    /*
        Тут мы будем проверять, пришёл ли нам корректный JSON с body
    */
    public function handle( $request, Closure $next )
    {
        if( !$request->has( 'body' )
            OR !is_string( $request->input( 'body' ) )
            OR !$body = json_decode( $request->input( 'body' ), TRUE )
            ):
            return response()->json( [ 'status' => 'error', 'HTTP' => '400 Bad Request' ] );
        endif;

        return $next( $request );
    }
}
