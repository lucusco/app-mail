<?php

require_once dirname(__DIR__) . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

class ConsumeQueue
{

    public static function consume()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'admin', 'admin');
        $channel = $connection->channel();

        $channel->queue_declare('emails_to_send', false, false, false, false);

        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
        };

        $channel->basic_consume('emails_to_send', '', false, true, false, false, $callback);

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}

ConsumeQueue::consume();
