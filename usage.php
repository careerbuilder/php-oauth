<?php
//Copyright 2016 CareerBuilder, LLC

require('bootstrap.php');

use CareerBuilder\OAuth2\OAuth2Plugin;
use CareerBuilder\OAuth2\NullTokenStorage;
use CareerBuilder\OAuth2\Flows\ClientCredentials;
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

$configs = array(
    'client_id' => '',
    'client_secret' => '',
    'shared_secret' => '',
    'base_url' => 'https://api.careerbuilder.com'
);

$logger = new Logger();

$client = new Client('https://api.careerbuilder.com');
$client->addSubscriber(new OAuth2Plugin(new ClientCredentials($configs, null, $logger), new NullTokenStorage()));
$client->addSubscriber(new LogPlugin(new PsrLogAdapter($logger)));

$request = $client->get('/some/api/route');
$response = $request->send();
$data = $response->json();
print_r($data);
