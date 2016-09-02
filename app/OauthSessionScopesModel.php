<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class OauthSessionScopesModel extends Eloquent
{
    protected $collection = 'oauth_session_scopes';
}
