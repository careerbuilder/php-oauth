<?php
// Copyright 2016 CareerBuilder, LLC
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and limitations under the License

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
