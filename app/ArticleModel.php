<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class ArticleModel extends Eloquent
{
	protected $collection = 'article';
}