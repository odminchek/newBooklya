<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class OauthAccessTokensModel extends Model
{
    protected $collection = 'oauth_access_tokens';
}
