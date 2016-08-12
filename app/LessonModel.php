<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class LessonModel extends Eloquent
{
	protected $collection = 'lesson';
}