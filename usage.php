<?php

require('bootstrap.php');

use CareerBuilder\OAuth2\OAuth2Plugin;
use CareerBuilder\OAuth2\TokenFactory;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\ServerErrorResponseException;

$config = array(
    'base_url' => 'https://www.careerbuilder.com',
    'client_id' => '',
    'client_secret' => '',
    'shared_secret' => ''
);

$client = new Client('https://api.careerbuilder.com');
$client->addSubscriber(new OAuth2Plugin(new TokenFactory($config)));

$request = $client->get('/corporate/geography/validate', array(
    'query' => array(
        'query' => 'Atlanta'
    )
));

$response = $request->send();
$data = $response->json();
echo $data;
