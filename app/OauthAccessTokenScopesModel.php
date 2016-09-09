<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class OauthAccessTokenScopesModel extends Eloquent
{
    protected $collection = 'oauth_access_token_scopes';
}
