<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class WebinarModel extends Eloquent
{
	protected $collection = 'webinar';
}