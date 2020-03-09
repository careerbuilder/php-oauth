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
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Firebase\JWT\JWT;

/**
 * Base class for all oAuth 2 flows.
 *
 * @package CareerBuilder\OAuth2\Flows
 */
abstract class Flow
{
    const BASE_URI = 'https://api.careerbuilder.com';

    /** @var ClientInterface */
    protected $client;

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

    /** @var string */
    private $tokenRequestPath;

    /**
     * @param array $configs
     * @param ClientInterface $client
     */
    public function __construct(array $configs, ClientInterface $client)
    {
        $configs = array_merge($this->getDefaultConfig(), $configs);
        $this->setCredentials($configs);
        $this->setDefaults();

        if ($configs['auth_in_header']) {
            $this->headers['Authorization'] = $this->getAuthHeader();
        }

        $this->client = $client;
        $this->tokenRequestPath = $configs['token_request_path'];
    }

    /**
     * @return array
     */
    private function getDefaultConfig()
    {
        return [
            'client_id' => '',
            'client_secret' => '',
            'shared_secret' => '',
            'token_request_path' => '/oauth/token',
            'auth_in_header' => false
        ];
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
        $this->headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
        $this->body = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        ];
    }

    /**
     * Build the authorization header for client information in header
     */
    private function getAuthHeader()
    {
        return sprintf(
            'Basic %s',
            base64_encode(sprintf('%s:%s', $this->clientId, $this->clientSecret))
        );
    }

    /**
     * @param AccessToken $token
     * @return AccessToken
     * @throws \Exception
     */
    public function getToken(AccessToken $token = null)
    {
        if ($token && $token->getRefreshToken()) {
            $this->body['grant_type'] = 'refresh_token';
            $this->body['refresh_token'] = $token->getRefreshToken();
        } else {
            $this->buildBody();
        }

        /** @var Response $response */
        $response = $this->client->post($this->tokenRequestPath, [
            'headers' => $this->headers,
            'form_params' => $this->body
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        $data = $data['data'] ?: [];
        $refreshToken = isset($data['refresh_token']) ?: '';

        return new AccessToken($data['access_token'], $refreshToken, $data['expires_in']);
    }

    protected abstract function buildBody();

    /**
     * Encode the claims into a JWT and sign using the HS512 algorithm
     *
     * @param $claims
     * @return string
     */
    protected function getJWT($claims)
    {
        return JWT::encode($claims, $this->sharedSecret, 'HS512');
    }
}

/**
 * Throws an exception upon JSON decode for PHP < 7.3
 *
 * @param $json
 * @param bool $assoc
 * @param int $depth
 * @param int $options
 * @return mixed
 * @throws \Exception
 * @deprecated Remove when upgrading to PHP 7.3
 */
function json_decode ($json, $assoc = false, $depth = 512, $options = 0) {
    $data = \json_decode($json, $assoc, $depth, $options);

    if (json_last_error() !== JSON_ERROR_NONE) {
        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $errorString = 'JSON_ERROR_DEPTH - The maximum stack depth has been exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $errorString =  'JSON_ERROR_STATE_MISMATCH - Invalid or malformed JSON';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $errorString =  'JSON_ERROR_CTRL_CHAR - Control character error, possibly incorrectly encoded';
                break;
            case JSON_ERROR_SYNTAX:
                $errorString =  'JSON_ERROR_SYNTAX - Syntax error';
                break;
            case JSON_ERROR_UTF8:
                $errorString =  'JSON_ERROR_UTF8 - Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $errorString =  json_last_error() . ' - Unknown/unlisted error';
        }
        throw new \Exception('JSON Error: ' . $errorString);
    }

    return $data;
}
