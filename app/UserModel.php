<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class UserModel extends Eloquent
{
	protected $collection = 'user';
}