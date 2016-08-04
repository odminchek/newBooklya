<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class SubjectModel extends Eloquent
{
	protected $collection = 'subject';
}