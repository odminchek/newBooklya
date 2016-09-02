<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class OauthAccessTokensModel extends Eloquent
{
    protected $collection = 'oauth_access_tokens';
}
