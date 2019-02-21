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

$connection = new AMQPStreamConnection($host, $port, $user, $password, $vHost);
$channel = $connection->channel();

$channel->queue_declare($queue, false, true, false, false);

$channel->exchange_declare($exchange, 'direct', false, true, false);
$channel->queue_bind($queue, $exchange);

//$messageBody = implode(' ', array_slice($argv, 1));

$faker = Faker\Factory::create();

$limit = 20;
$iteration = 0;

while($iteration <= $limit)
{
    $messageBody = json_encode([
        'name'          =>  $faker->name,
        'email'         =>  $faker->email,
        'subscribed'    =>  true,
    ]);

    $message = new AMQPMessage($messageBody, [
        'content_type' => 'application/json',
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
    ]);

    $channel->basic_publish($message, $exchange);

    $iteration++;
}

echo "finished publishing to queue: ".$queue.PHP_EOL;

$channel->close();
$connection->close();