<?php

namespace CareerBuilder\OAuth2;

use CareerBuilder\OAuth2\Flows\Flow;
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
     * @var Flow
     */
    private $flow;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param Flow $flow
     */
    public function __construct(Flow $flow, TokenStorageInterface $tokenStorage)
    {
        $this->flow = $flow;
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
            $this->token = $this->flow->getToken();
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
