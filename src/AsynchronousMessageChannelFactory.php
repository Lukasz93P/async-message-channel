<?php


namespace Lukasz93P\AsyncMessageChannel;


use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Log\LoggerInterface;

final class AsynchronousMessageChannelFactory
{
    /**
     * @var AMQPChannel
     */
    private static $channel;

    private function __construct()
    {
    }

    public static function withLogger(LoggerInterface $logger): AsynchronousMessageChannel
    {
        self::initializeChannel();

        return RabbitMqMessageChannel::withLoggerAndChannel($logger, self::$channel);
    }

    private static function initializeChannel(): void
    {
        self::$channel = self::$channel ?? (new AMQPStreamConnection(
                getenv('RABBIT_MQ_HOST') ?: 'localhost',
                getenv('RABBIT_MQ_PORT') ?: 5672,
                getenv('RABBIT_MQ_USER') ?: 'guest',
                getenv('RABBIT_MQ_PASSWORD') ?: 'guest'
            ))->channel();
    }

}