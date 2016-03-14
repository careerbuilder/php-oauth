<?php
//Copyright 2016 CareerBuilder, LLC

namespace CareerBuilder\OAuth2\Flows;

use Guzzle\Http\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * JWT-Bearer Assertion Flow
 *
 * @package CareerBuilder\OAuth2\Flows
 */
class JWTBearerAssertion extends Flow
{
    /** @var string */
    private $email;
    /** @var string */
    private $accountId;

    /**
     * @param array $configs
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     */
    public function __construct(array $configs, ClientInterface $client = null, LoggerInterface $logger = null)
    {
        parent::__construct($configs, $client, $logger);
        $this->email = $configs['email'];
        $this->accountId = $configs['account_id'];
    }

    /**
     * Build token request body for the flow
     */
    protected function buildBody()
    {
        $this->body['grant_type'] = 'urn:ietf:params:oauth:grant-type:jwt-bearer';
        $this->body['assertion'] = $this->getJWT($this->getJWTBearerClaims());
    }

    /**
     * Get the clains for the flow
     */
    private function getJWTBearerClaims()
    {
        return array(
            'iss' => $this->clientId,
            'sub' => "{$this->email}:{$this->accountId}",
            'aud' => 'www.careerbuilder.com/share/oauth2'
            'exp' => time() + 30
        );
    }
}