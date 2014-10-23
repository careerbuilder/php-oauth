<?php

namespace CareerBuilder\OAuth2;

class AccessToken
{
    private $token;
    private $refreshToken;
    private $expiresIn;

    public function __construct($token, $refreshToken, $expiresIn)
    {
        $this->token = $token;
        $this->refreshToken = $refreshToken;
        $this->expiresIn = $expiresIn;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function isExpired()
    {
        return time() > $this->expiresIn;
    }

    public function __toString()
    {
        return $this->token;
    }
}
