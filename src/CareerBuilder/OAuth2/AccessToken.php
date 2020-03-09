<?php
// Copyright 2016 CareerBuilder, LLC
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and limitations under the License

namespace CareerBuilder\OAuth2;

class AccessToken
{
    /** @var string */
    private $token;

    /** @var string */
    private $refreshToken;

    /** @var int */
    private $expiresAt;

    /**
     * AccessToken constructor.
     *
     * @param string $token
     * @param string $refreshToken
     * @param int $expiresIn
     */
    public function __construct($token, $refreshToken, $expiresIn)
    {
        $this->token = $token;
        $this->refreshToken = $refreshToken;
        $this->expiresAt = time() + $expiresIn;
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return time() > $this->expiresAt;
    }

    /**
     * @return int
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->token;
    }
}
