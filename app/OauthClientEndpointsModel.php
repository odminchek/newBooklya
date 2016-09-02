<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class OauthClientEndpointsModel extends Model
{
    protected $collection = 'oauth_client_endpoints';
}
