<?php

namespace CareerBuilder\OAuth2;

use Guzzle\Http\Client;
use Guzzle\Http\ClientInterface;
use Guzzle\Plugin\Log\LogPlugin;
use Guzzle\Log\PsrLogAdapter;
use JWT;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class TokenFactory
{
    private $clientId;
    private $clientSecret;
    private $sharedSecret;
    private $scope;
    private $client;
    private $logger;

    /**
     * @param array $config
     * @param ClientInterface $client
     */
    public function __construct($config, ClientInterface $client = null, LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
        $this->client = $client ?: new Client();
        $this->client->setBaseUrl($config['base_url']);
        $this->client->addSubscriber(new LogPlugin(new PsrLogAdapter($this->logger)));
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->sharedSecret = $config['shared_secret'];
        $this->scope = isset($config['scope']) ? $config['scope'] : null;
    }

    /**
     * @param AccessToken $token optional, passing in returns a refreshed token
     * @return AccessToken
     */
    public function getToken(AccessToken $token = null)
    {
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded'
        );
        $body = array(
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        );

        if ($token && $token->getRefreshToken()) {
            $body['grant_type'] = 'refresh_token';
            $body['refresh_token'] = $token->getRefreshToken();
        } else {
            $body['client_assertion_type'] = 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer';
            $body['client_assertion'] = $this->getJWT();
            $body['grant_type'] = 'client_credentials';
            if ($this->scope) {
                $body['scope'] = $this->scope;
            }
        }

        $request = $this->client->post('/share/oauth2/token.aspx', $headers, $body);
        $response = $request->send();
        $data = $response->json();
        return new AccessToken($data['access_token'], '', $data['expires_in']);
    }

    private function getJWT()
    {
        return JWT::encode(array(
            'iss' => $this->clientId,
            'sub' => $this->clientId,
            'aud' => 'http://www.careerbuilder.com/share/oauth2/token.aspx',
            'exp' => time() + (30 * 60)
        ), $this->sharedSecret);
    }
}
