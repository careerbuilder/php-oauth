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

namespace CareerBuilder\OAuth2\Flows;

use GuzzleHttp\ClientInterface;

/**
 * JWT-Bearer Assertion Flow
 *
 * @package CareerBuilder\OAuth2\Flows
 */
class JWTBearerAssertion extends Flow
{
    /** @var string */
    private $email;

    /** @var string */
    private $accountId;

    /**
     * @param array $configs
     * @param ClientInterface $client
     */
    public function __construct(array $configs, ClientInterface $client)
    {
        parent::__construct($configs, $client);
        $this->email = $configs['email'];
        $this->accountId = $configs['account_id'];
    }

    /**
     * Build token request body for the flow
     */
    protected function buildBody()
    {
        $this->body['grant_type'] = 'urn:ietf:params:oauth:grant-type:jwt-bearer';
        $this->body['assertion'] = $this->getJWT($this->getJWTBearerClaims());
    }

    /**
     * Get the clains for the flow
     * @return array
     */
    private function getJWTBearerClaims()
    {
        return [
            'iss' => $this->clientId,
            'sub' => sprintf('%s:%s', $this->email, $this->accountId),
            'aud' => 'www.careerbuilder.com/share/oauth2',
            'exp' => time() + 30
        ];
    }
}
