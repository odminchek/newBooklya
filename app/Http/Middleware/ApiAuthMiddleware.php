<?php

namespace App\Http\Middleware;

use App\UserAuthModel;
use Closure;

class ApiAuthMiddleware
{
    /*
        Проверяем, аутентифицирован ли пользователь
    */
    public function handle($request, Closure $next)
    {
        // проверка входных данных
        if( !$body = json_decode( $request->input( 'body' ), TRUE )
            // user_id
            OR !isset( $body[ 'user_id' ] )
            OR !is_string( $body[ 'user_id' ] )
            OR mb_strlen( $body[ 'user_id' ] ) !== 24
            OR !ctype_xdigit( $body[ 'user_id' ] )
            // auth_key
            OR !isset( $body[ 'auth_key' ] )
            OR !is_string( $body[ 'auth_key' ] )
            OR mb_strlen( $body[ 'auth_key' ] ) !== 64
            ):
            return response()->json( [ 'status' => 'error', 'HTTP' => '400 Bad request' ] );
        endif;

        // проверка аутентификации
        if( !$userAuth = UserAuthModel::where( 'userId', '=', $body[ 'user_id' ] )->first()
            OR $userAuth->authKey !== $body[ 'auth_key' ]
            ):
            return response()->json( [ 'status' => 'error', 'HTTP' => '401 Unauthorized' ] );
        endif;

        return $next($request);
    }
}


/*

        if( !$this->isMongoId( $userId )
            OR !$userAuth = UserAuthModel::where( 'userId', '=', $userId )->first()
            ):
            return FALSE;
        endif;

        return $userAuth;


    if( !is_string( $mongoId )
            OR mb_strlen( $mongoId ) !== 24
            OR !ctype_xdigit( $mongoId )
            ):
            return FALSE;
        endif;


*/