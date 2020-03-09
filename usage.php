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

require_once('vendor/autoload.php');

use CareerBuilder\OAuth2\Flows\Flow;
use CareerBuilder\OAuth2\OAuth2Plugin;
use CareerBuilder\OAuth2\NullTokenStorage;
use CareerBuilder\OAuth2\Flows\ClientCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Log\Test\TestLogger;

$credClient = new ClientCredentials([
    'client_id' => 'yourclientid',
    'client_secret' => 'shhh',
    'shared_secret' => 'supersecret',
], new Client(['base_uri' => 'https://www.careerbuilder.com']));


$stack = HandlerStack::create();
$stack->push(new OAuth2Plugin($credClient, new NullTokenStorage()));
$stack->push(
    Middleware::log(new TestLogger(), new MessageFormatter('{req_body} - {res_body}')),
    'OAuth2ClientLogger'
);

// Create Guzzle client as you normally do
$client = new Client([
    'base_uri' => Flow::BASE_URI,
    'handler' => $stack
]);

$response = $client->get('some/api/route');
var_dump($response->getBody()->getContents());
