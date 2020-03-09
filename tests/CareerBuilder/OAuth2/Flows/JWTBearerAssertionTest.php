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

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class JWTBearerAssertionTest extends TestCase
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

        $flow = new JWTBearerAssertion([
            'client_id' => 'clientid',
            'client_secret' => 'clientsecret',
            'shared_secret' => 'sharedsecret',
            'email' => 'email@example.com',
            'account_id' => 'accountid'
        ], $client);

        $token = $flow->getToken();

        $request = $mock->getLastRequest();
        $body = $request->getBody()->getContents();

        $this->assertNotEmpty($body);

        $postFields = [];
        parse_str($body, $postFields);

        $jwt = JWT::decode($postFields['assertion'], 'sharedsecret', ['HS512']);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('clientid', $postFields['client_id']);
        $this->assertEquals('clientsecret', $postFields['client_secret']);
        $this->assertEquals('urn:ietf:params:oauth:grant-type:jwt-bearer', $postFields['grant_type']);
        $this->assertEquals('clientid', $jwt->iss);
        $this->assertEquals('email@example.com:accountid', $jwt->sub);
        $this->assertEquals('www.careerbuilder.com/share/oauth2', $jwt->aud);
        $this->assertEquals(time() + 30, $jwt->exp);
        $this->assertEquals('hi', (string) $token);
        $this->assertEquals(true, $token->getRefreshToken()); // TODO
        $this->assertEquals(time() + 1, $token->getExpiresAt());
    }
}
