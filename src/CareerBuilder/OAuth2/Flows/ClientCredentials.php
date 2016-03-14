<?php
//Copyright 2016 CareerBuilder, LLC

namespace CareerBuilder\OAuth2\Flows;

use Guzzle\Http\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Builds the body for the Client Credentials flow using JWT-Bearer
 * assertions for client authentication.
 *
 * @package CareerBuilder\OAuth2\Flows
 */
class ClientCredentials extends Flow
{
    /**
     * @param array $configs
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     */
    public function __construct(array $configs, ClientInterface $client = null, LoggerInterface $logger = null)
    {
        parent::__construct($configs, $client, $logger);
    }

    /**
     * Build the body for the token request
     */
    public function buildBody()
    {
        $this->body['grant_type'] = 'client_credentials';
        $this->body['client_assertion_type'] = 'urn:params:oauth:client-assertion-type:jwt-bearer';
        $this->body['client_assertion'] = $this->getJWT($this->getClientCredentialsClaims());
    }

    /**
     * Get the array of JWT claims for the flow
     */
    private function getClientCredentialsClaims()
    {
        return array(
            'iss' => $this->clientId,
            'sub' => $this->clientId,
            'aud' => 'https://api.careerbuilder.com/oauth/token',
            'exp' => time() + 180
        );
    }
}
