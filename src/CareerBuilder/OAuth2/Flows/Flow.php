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

use CareerBuilder\OAuth2\AccessToken;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\Client;
use Guzzle\Plugin\Log\LogPlugin;
use Guzzle\Log\PsrLogAdapter;
use JWT;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Base class for all oAuth 2 flows.
 *
 * @package CareerBuilder\OAuth2\Flows
 */
abstract class Flow
{
    /** @var ClientInterface */
    protected $client;
    /** @var LoggerInterface */
    protected $logger;
    /** @var string */
    protected $clientId;
    /** @var string */
    protected $clientSecret;
    /** @var string */
    protected $sharedSecret;
    /** @var array */
    protected $headers;
    /** @var array */
    protected $body;

    /**
     * @param array $configs
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     */
    protected function __construct(array $configs, ClientInterface $client = null, LoggerInterface $logger = null)
    {
        $this->setCredentials($configs);
        $this->setDefaults();
        

        if (isset($configs['auth_in_header']) && $configs['auth_in_header']) {
            $this->headers['Authorization'] = $this->getAuthHeader();
        }

        $this->logger = $logger ?: new NullLogger();
        $this->client = $client ?: new Client();
        $this->client->setBaseUrl($configs['base_url']);
        $this->client->addSubscriber(new LogPlugin(new PsrLogAdapter($this->logger)));
    }

    /**
     * @param array $configs
     */
    private function setCredentials(array $configs)
    {
        $this->clientId = $configs['client_id'];
        $this->clientSecret = $configs['client_secret'];
        $this->sharedSecret = $configs['shared_secret'];
    }

    /**
     * Set default headers and body
     */
    private function setDefaults()
    {
        $this->headers = array('Content-Type' => 'application/x-www-form-urlencoded');
        $this->body = array(
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        );
    }

    /**
     * Build the authorization header for client information in header
     */
    private function getAuthHeader()
    {
        $unencodedParams = "{$this->clientId}:{$this->clientSecret}";
        $encodedParams = base64_encode($unencodedParams);

        return "Basic {$encodedParams}";
    }

    /**
     * @param AccessToken $token
     * @return AcccessToken
     */
    public function getToken(AccessToken $token = null)
    {
        if ($token && $token->getRefreshToken()) {
            $this->body['grant_type'] = 'refresh_token';
            $this->body['refresh_token'] = $token->getRefreshToken();
        } else {
            $this->buildBody();
        }

        $request = $this->client->post('/oauth/token', $this->headers, $this->body);
        $response = $request->send();
        $data = $response->json();

        $refreshToken = isset($data['refresh_token']) ?: '';

        return new AccessToken($data['access_token'], $refreshToken, $data['expires_in']);
    }

    protected abstract function buildBody();

    /**
     * Encode the claims into a JWT and sign using the HS512 algorithm
     */
    protected function getJWT($claims)
    {
        return JWT::encode($claims, $this->sharedSecret, 'HS512');
    }
}