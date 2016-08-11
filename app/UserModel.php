<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class UserModel extends Eloquent
{
	protected $collection = 'user';

	// переопределение функции find() взамен неработающей родной
	public static function find( $mongo_id )
	{
		if( !is_string( $mongo_id )
			OR !mb_strlen( $mongo_id )
			OR !ctype_xdigit( $mongo_id )
			):
			return FALSE;
		endif;

		return Self::whereRaw( [ '_id' => $mongo_id ] )->first();
	}
}