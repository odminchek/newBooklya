<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class MessageModel extends Eloquent
{
	protected $collection = 'message';
}