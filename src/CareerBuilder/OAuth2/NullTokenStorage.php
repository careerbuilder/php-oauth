<?php

namespace CareerBuilder\OAuth2;

use CareerBuilder\OAuth2\AccessToken;

class NullTokenStorage implements TokenStorageInterface
{
    public function store(AccessToken $token)
    {
    }

    public function fetch()
    {
    }
}
