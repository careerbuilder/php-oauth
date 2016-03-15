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

use Guzzle\Http\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Builds the body for the Client Credentials flow using JWT-Bearer
 * assertions for client authentication.
 *
 * @package CareerBuilder\OAuth2\Flows
 */
class ClientCredentials extends Flow
{
    /**
     * @param array $configs
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     */
    public function __construct(array $configs, ClientInterface $client = null, LoggerInterface $logger = null)
    {
        parent::__construct($configs, $client, $logger);
    }

    /**
     * Build the body for the token request
     */
    public function buildBody()
    {
        $this->body['grant_type'] = 'client_credentials';
        $this->body['client_assertion_type'] = 'urn:params:oauth:client-assertion-type:jwt-bearer';
        $this->body['client_assertion'] = $this->getJWT($this->getClientCredentialsClaims());
    }

    /**
     * Get the array of JWT claims for the flow
     */
    private function getClientCredentialsClaims()
    {
        return array(
            'iss' => $this->clientId,
            'sub' => $this->clientId,
            'aud' => 'https://api.careerbuilder.com/oauth/token',
            'exp' => time() + 180
        );
    }
}
