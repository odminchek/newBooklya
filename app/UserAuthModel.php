<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class UserAuthModel extends Eloquent
{
	protected $collection = 'user-auth';
}