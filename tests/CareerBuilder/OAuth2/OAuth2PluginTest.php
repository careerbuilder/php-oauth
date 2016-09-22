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

use CareerBuilder\OAuth2\Flows\ClientCredentials;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use PHPUnit\Framework\TestCase;

class OAuth2PluginTest extends TestCase
{
    public function testTokenOnRequest()
    {
        $flowMockPlugin = new MockPlugin();
        $flowMockPlugin->addResponse(new Response(200, array(), json_encode(array(
            'data' => array(
                'access_token' => 'accesstokenhere',
                'expires_in' => 1,
                'refresh_token' => 'refresh'
            )
        ))));

        $flowClient = new Client();
        $flowClient->addSubscriber($flowMockPlugin);

        $flow = new ClientCredentials(array(
            'client_id' => 'clientid',
            'client_secret' => 'clientsecret',
            'shared_secret' => 'sharedsecret'
        ), $flowClient);

        $oauthPlugin = new OAuth2Plugin($flow, new NullTokenStorage());

        $mockPlugin = new MockPlugin();
        $mockPlugin->addResponse(new Response(200, array(), json_encode(array('data' => 'data'))));

        $client = new Client();
        $client->addSubscriber($oauthPlugin);
        $client->addSubscriber($mockPlugin);

        $request = $client->get('https://api.careerbuilder.com');
        $response = $request->send();

        $this->assertEquals('Bearer accesstokenhere', (string)$request->getHeader('Authorization'));
        $this->assertEquals('data', $response->json()['data']);
    }
}
