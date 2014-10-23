<?php

require('bootstrap.php');

use CareerBuilder\OAuth2\OAuth2Plugin;
use CareerBuilder\OAuth2\TokenFactory;
use CareerBuilder\OAuth2\NullTokenStorage;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Psr\Log\LoggerInterface;
use Psr\Log\AbstractLogger;
use Guzzle\Plugin\Log\LogPlugin;
use Guzzle\Log\PsrLogAdapter;

class Logger extends AbstractLogger
{
    public function log($logLevel, $message, array $context = array())
    {
        print_r(array(
            'level' => $logLevel,
            'message' => $message
        ));
    }
}

$config = array(
    'base_url' => 'https://www.careerbuilder.com',
    'client_id' => '',
    'client_secret' => '',
    'shared_secret' => ''
);
$logger = new Logger();

$client = new Client('https://api.careerbuilder.com');
$client->addSubscriber(new OAuth2Plugin(new TokenFactory($config, null, $logger), new NullTokenStorage()));
$client->addSubscriber(new LogPlugin(new PsrLogAdapter($logger)));

$request = $client->get('/corporate/geography/validate');
$request->getQuery()->set('query', 'Atlanta');

$response = $request->send();
$data = $response->json();
print_r($data);
