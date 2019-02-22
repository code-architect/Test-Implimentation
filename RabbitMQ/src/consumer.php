<?php
require dirname(__DIR__).'/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$host = '';
$port = 5672;
$user = '';
$password = 'ceW-';
$vHost = '';
$exchange = 'rabbitMQ_test';
$queue = 'code_architect';

$consumerTag = 'consumer';
$connection = new AMQPStreamConnection($host, $port, $user, $password, $vHost);
$channel = $connection->channel();

$channel->queue_declare($queue, false, true, false, false);

$channel->exchange_declare($exchange, 'direct', false, true, false);
$channel->queue_bind($queue, $exchange);

/**
 * @param AMQPMessage $message
 */
function process_message(AMQPMessage $message)
{
   $messageBody = json_decode($message->body);
   $email = $messageBody->email;

   file_put_contents(dirname(__DIR__).'/data/'.$email.'.json', $message->body);


    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    // Send a message with the string "quit" to cancel the consumer.
//    if ($message->body === 'quit') {
//        $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
//    }
}

$consumerTag = 'local.windows.consumer';
$channel->basic_consume($queue, $consumerTag, false, false, false, false, 'process_message');
/**
 * @param \PhpAmqpLib\Channel\AMQPChannel $channel
 * @param \PhpAmqpLib\Connection\AbstractConnection $connection
 */
function shutdown($channel, $connection)
{
    $channel->close();
    $connection->close();
}
register_shutdown_function('shutdown', $channel, $connection);
// Loop as long as the channel has callbacks registered
while (count($channel->callbacks)) {
    $channel->wait();
}