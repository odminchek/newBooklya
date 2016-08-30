<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class OauthClientsModel extends Eloquent
{
    protected $collection = 'oauth_clients';
}
