<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class FeedbackModel extends Eloquent
{
	protected $collection = 'feedback';
}