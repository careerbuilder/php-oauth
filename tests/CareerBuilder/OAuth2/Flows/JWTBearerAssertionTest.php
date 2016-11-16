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
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use PHPUnit\Framework\TestCase;

class JWTBearerAssertionTest extends TestCase
{
    public function testGetToken()
    {
        $mockPlugin = new MockPlugin();
        $mockPlugin->addResponse(new Response(200, array(), json_encode(array(
            'data' => array(
                'access_token' => 'hi',
                'expires_in' => 1,
                'refresh_token' => 'refresh'
            )
        ))));

        $client = new Client();
        $client->addSubscriber($mockPlugin);

        $flow = new JWTBearerAssertion(array(
            'client_id' => 'clientid',
            'client_secret' => 'clientsecret',
            'shared_secret' => 'sharedsecret',
            'email' => 'email@example.com',
            'account_id' => 'accountid'
        ), $client);

        $token = $flow->getToken();
        $request = $mockPlugin->getReceivedRequests()[0];
        $postFields = $request->getPostFields();
        $jwt = JWT::decode($postFields['assertion'], 'sharedsecret', array('HS512'));

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('clientid', $postFields['client_id']);
        $this->assertEquals('clientsecret', $postFields['client_secret']);
        $this->assertEquals('urn:ietf:params:oauth:grant-type:jwt-bearer', $postFields['grant_type']);
        $this->assertEquals('clientid', $jwt->iss);
        $this->assertEquals('email@example.com:accountid', $jwt->sub);
        $this->assertEquals('www.careerbuilder.com/share/oauth2', $jwt->aud);
        $this->assertEquals(time() + 30, $jwt->exp);
        $this->assertEquals('hi', "$token");
        $this->assertEquals(true, $token->getRefreshToken()); // TODO
        $this->assertEquals(time() + 1, $token->getExpiresAt());
    }
}
