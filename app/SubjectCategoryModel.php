<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class SubjectCategoryModel extends Eloquent
{
	protected $collection = 'subject-category';
}
