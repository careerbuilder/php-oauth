<?php
//Copyright 2016 CareerBuilder, LLC

namespace CareerBuilder\OAuth2;

use CareerBuilder\OAuth2\AccessToken;

interface TokenStorageInterface
{
    public function store(AccessToken $token);

    public function fetch();
}
