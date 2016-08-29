<?php

namespace App;

use Illuminate\Support\Facades\Auth;

class PasswordGrantVerifier
{
    // проверяем соответствие логина и пароля
    public function verify( $username, $password )
    {
        // учётные данные
        $credentials = [
                'email' => $username,
                'password' => $password,
        ];

        if( !$userModel = UserModel::where( 'phoneNumber', '=', $username )->orWhere( 'primaryEmail', '=', $username )->first()
            OR !$user = $userModel->toArray()
            OR !is_array( $user )
            OR empty( $user )
            OR !isset( $user[ '_id' ] )
            ):
            return FALSE;
        endif;

        // проверяем пароль
        if( !isset( $user[ 'password' ] ) 
            OR !is_string( $user[ 'password' ] )
            OR !mb_strlen( $user[ 'password' ] )
            OR $user[ 'password' ] !== md5( $password )
            ):
            return FALSE;
        endif;

        return $user[ '_id' ];
    }
}