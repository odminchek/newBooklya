<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class OauthSessionsModel extends Model
{
    protected $collection = 'oauth_sessions';
}
