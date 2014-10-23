<?php

namespace CareerBuilder\OAuth2;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;
use Psr\Log\LoggerInterface;

class OAuth2Plugin implements EventSubscriberInterface
{
    /**
     * @var AccessToken
     */
    private $token;

    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @param TokenFactory $tokenFactory
     */
    public function __construct(TokenFactory $tokenFactory)
    {
        $this->tokenFactory = $tokenFactory;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'request.before_send' => 'onBeforeSend',
            'request.complete' => 'onComplete'
        );
    }

    public function onBeforeSend(Event $event)
    {
        if (!$this->token) {
            $this->token = $this->tokenFactory->getToken();
        }
        if ($this->token->isExpired()) {
            $this->token = $this->tokenFactory->getToken();
        }
        $request = $event['request'];
        $request->setHeader('Authorization', sprintf('Bearer %s', $this->token));
    }

    public function onComplete(Event $event)
    {
        $response = $event['response'];
        echo (string)$response->getBody();
    }
}
