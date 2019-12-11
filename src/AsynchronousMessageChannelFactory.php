<?php
declare(strict_types=1);


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

        return MessageChannelWhichNotAllowToRunMoreThanOneProcessOneQueue::decoratorForAsynchronousMessageChannel(
            RabbitMqMessageChannel::withLoggerAndChannel($logger, self::$channel)
        );
    }

    private static function initializeChannel(): void
    {
        self::$channel = self::$channel ?? (new AMQPStreamConnection(
                getenv('MQ_BROKER_HOST') ?: 'localhost',
                getenv('MQ_BROKER_PORT') ?: 5672,
                getenv('MQ_BROKER_USER') ?: 'guest',
                getenv('MQ_BROKER_PASSWORD') ?: 'guest'
            ))->channel();
    }

}