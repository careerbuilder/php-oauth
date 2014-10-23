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
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param TokenFactory $tokenFactory
     */
    public function __construct(TokenFactory $tokenFactory, TokenStorageInterface $tokenStorage)
    {
        $this->tokenFactory = $tokenFactory;
        $this->tokenStorage = $tokenStorage;
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
            $this->token = $this->tokenStorage->fetch();
        }
        if (!$this->token || $this->token->isExpired()) {
            $this->token = $this->tokenFactory->getToken();
            $this->tokenStorage->store($this->token);
        }
        $request = $event['request'];
        $request->setHeader('Authorization', sprintf('Bearer %s', $this->token));
    }

    public function onComplete(Event $event)
    {
        $response = $event['response'];
    }
}
