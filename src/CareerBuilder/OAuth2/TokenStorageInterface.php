<?php

namespace CareerBuilder\OAuth2;

use CareerBuilder\OAuth2\AccessToken;

interface TokenStorageInterface
{
    public function store(AccessToken $token);

    public function fetch();
}
