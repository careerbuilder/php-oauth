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
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ClientCredentialsTest extends TestCase
{
    public function testGetToken()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'data' => [
                    'access_token' => 'hi',
                    'expires_in' => 1,
                    'refresh_token' => 'refresh'
                ]
            ]))
        ]);

        $client = new Client([
            'base_uri' => 'https://api.careerbuilder.com',
            'handler' => $mock
        ]);

        $flow = new ClientCredentials([
            'client_id' => 'clientid',
            'client_secret' => 'clientsecret',
            'shared_secret' => 'sharedsecret'
        ], $client);

        $token = $flow->getToken();

        $request = $mock->getLastRequest();
        $body = $request->getBody()->getContents();

        $this->assertNotEmpty($body);

        $postFields = [];
        parse_str($body, $postFields);

        $jwt = JWT::decode($postFields['client_assertion'], 'sharedsecret', ['HS512']);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('clientid', $postFields['client_id']);
        $this->assertEquals('clientsecret', $postFields['client_secret']);
        $this->assertEquals('client_credentials', $postFields['grant_type']);
        $this->assertEquals('urn:params:oauth:client-assertion-type:jwt-bearer', $postFields['client_assertion_type']);
        $this->assertEquals('clientid', $jwt->iss);
        $this->assertEquals('clientid', $jwt->sub);
        $this->assertEquals('https://api.careerbuilder.com/oauth/token', $jwt->aud);
        $this->assertEquals(time() + 180, $jwt->exp);
        $this->assertEquals('hi', (string) $token);
        $this->assertEquals(true, $token->getRefreshToken()); // TODO
        $this->assertEquals(time() + 1, $token->getExpiresAt());
    }

    public function testGetTokenWithRefresh()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'data' => [
                    'access_token' => 'hi',
                    'expires_in' => 1,
                    'refresh_token' => 'refresh'
                ]
            ]))
        ]);

        $client = new Client([
            'base_uri' => 'https://api.careerbuilder.com',
            'handler' => $mock
        ]);

        $flow = new ClientCredentials([
            'client_id' => 'clientid',
            'client_secret' => 'clientsecret',
            'shared_secret' => 'sharedsecret',
            'auth_in_header' => true
        ], $client);

        $token = $flow->getToken(new AccessToken('token', 'refresh', 1));

        $request = $mock->getLastRequest();
        $body = $request->getBody()->getContents();

        $this->assertNotEmpty($body);

        $postFields = [];
        parse_str($body, $postFields);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('clientid', $postFields['client_id']);
        $this->assertEquals('clientsecret', $postFields['client_secret']);
        $this->assertEquals('refresh_token', $postFields['grant_type']);
        $this->assertEquals('refresh', $postFields['refresh_token']);
        $this->assertEquals('hi', "$token");
        $this->assertEquals(true, $token->getRefreshToken()); // TODO
        $this->assertEquals(time() + 1, $token->getExpiresAt());
    }
}
