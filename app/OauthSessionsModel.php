<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class OauthSessionsModel extends Eloquent
{
    protected $collection = 'oauth_sessions';
}
