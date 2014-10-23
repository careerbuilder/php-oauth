<?php

require('bootstrap.php');

use CareerBuilder\OAuth2\OAuth2Plugin;
use CareerBuilder\OAuth2\TokenFactory;
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

$client = new Client('https://wwwtest.api.careerbuilder.com');
$client->addSubscriber(new OAuth2Plugin(new TokenFactory($config, null, $logger)));
$client->addSubscriber(new LogPlugin(new PsrLogAdapter($logger)));

$request = $client->get('/corporate/geography/validate', array(
    'query' => array(
        'query' => 'Atlanta'
    )
));

$response = $request->send();
$data = $response->json();
echo $data;
