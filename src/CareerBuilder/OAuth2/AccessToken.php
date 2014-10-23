<?php

namespace CareerBuilder\OAuth2;

class AccessToken
{
    private $token;
    private $refreshToken;
    private $expiresAt;

    public function __construct($token, $refreshToken, $expiresIn)
    {
        $this->token = $token;
        $this->refreshToken = $refreshToken;
        $this->expiresAt = time() + $expiresIn;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function isExpired()
    {
        return time() > $this->expiresAt;
    }

    public function __toString()
    {
        return $this->token;
    }
}
