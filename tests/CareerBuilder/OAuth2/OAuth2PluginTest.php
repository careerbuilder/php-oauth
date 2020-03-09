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
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class OAuth2PluginTest extends TestCase
{
    public function testTokenOnRequest()
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode(['data' => 'data']))
        ]);

        $stack = new HandlerStack($handler);
        $stack->push(new OAuth2Plugin(
            $this->getClientCredentials(),
            new NullTokenStorage()
        ));

        $client = new Client([
            'base_uri' => 'https://api.careerbuilder.com',
            'handler' => $stack
        ]);

        $response = $client->get('https://api.careerbuilder.com');
        $response = json_decode($response->getBody()->getContents(), true);

        $request = $handler->getLastRequest();

        $this->assertEquals('Bearer accesstokenhere', $request->getHeader('Authorization')[0]);
        $this->assertEquals('data', $response['data']);
    }

    /**
     * @return ClientCredentials
     */
    private function getClientCredentials()
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([
                'data' => [
                    'access_token' => 'accesstokenhere',
                    'expires_in' => 1,
                    'refresh_token' => 'refresh'
                ]
            ]))
        ]);

        return new ClientCredentials([
            'client_id' => 'clientid',
            'client_secret' => 'clientsecret',
            'shared_secret' => 'sharedsecret'
        ], new Client([
            'base_uri' => 'https://api.careerbuilder.com',
            'handler' => new HandlerStack($handler)
        ]));
    }
}
