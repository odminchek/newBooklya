<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class OauthClientEndpointsModel extends Eloquent
{
    protected $collection = 'oauth_client_endpoints';
}
