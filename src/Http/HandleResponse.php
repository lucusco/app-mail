<?php

namespace App\Mail\Http;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HandleResponse implements EventSubscriberInterface
{
    public function onResponse(ResponseEvent $event)
    {
        // do something
    }

    public static function getSubscribedEvents()
    {
        return ['response' => 'onResponse'];
    }
}