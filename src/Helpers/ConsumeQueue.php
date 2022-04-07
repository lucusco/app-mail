<?php

require_once dirname(__DIR__) . '/../vendor/autoload.php';

require SRC_DIR . 'config/loadEnvs.php';

use App\Mail\Classes\Log;
use App\Mail\Classes\Email;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/* 
 * Class to consume the queue and call function to send email
 */
class ConsumeQueue
{
    private Log $log;

    public function __construct()
    {
        $this->log = new Log(LOG::LOG_QUEUE);
        $this->log->addMessage('info', "Initializing...\n");
        exec('touch ' . QUEUE_CONTROL_FILE);
    }


    /**
     * Consume queue main function
     */
    public function consume(): void
    {
        try {
            $connection = new AMQPStreamConnection('localhost', 5672, 'admin', 'admin');
            $channel = $connection->channel();
            $channel->queue_declare('emails_to_send', false, false, false, false);

            $callback = function ($msg) {
                $data = (array)(json_decode($msg->body));
                extract($data);
                $this->log->addMessage('info', "[x] New E-mail to Send - ID: $id\n");
                Email::send($id);
            };

            $channel->basic_consume('emails_to_send', '', false, true, false, false, $callback);
            while ($channel->is_open()) {
                $channel->wait();
            }

            $channel->close();
            $connection->close();
        } catch (Exception $e) { // When containers are stopped
            self::removeControlFile();
        }
    }

    /**
     * Remove control file
     */
    private static function removeControlFile(): void
    {
        clearstatcache();
        if (file_exists(QUEUE_CONTROL_FILE)) {
            unlink(QUEUE_CONTROL_FILE);
        }
    }

    public function __destruct()
    {
        self::removeControlFile();
    }
}

clearstatcache();
if (file_exists(QUEUE_CONTROL_FILE) === false) {
    $queue = new ConsumeQueue();
    $queue->consume();
}
