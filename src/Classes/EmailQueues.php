<?php

namespace App\Mail\Classes;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class EmailQueues
{
    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;

    private const EMAIL_SEND = 'emails_to_send';

    public function __construct()
    {
        // Create Connection and get channel
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'admin', 'admin');
        $this->channel = $this->connection->channel();

        // Create queues
        $this->channel->queue_declare(self::EMAIL_SEND, false, false, false, false);
    }

    public function publishMessageToSendEmail(string $msg)
    {
        $message = new AMQPMessage($msg);
        $this->channel->basic_publish($message, '', self::EMAIL_SEND);
    }

    public function consumeQueue()
    {
        $callback = function ($msg) {
            echo '[x] Received ', $msg->body, "\n";
        };

        $this->channel->basic_consume(self::EMAIL_SEND, '', false, true, false, false, $callback);

        while ($this->channel->is_open()) {
            $this->channel->wait();
        }
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
