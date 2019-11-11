<?php


namespace Lukasz93P\AsyncMessageChannel;


use Psr\Log\LoggerInterface;

final class AsynchronousMessageChannelFactory
{
    private function __construct()
    {
    }

    public static function withLogger(LoggerInterface $logger): AsynchronousMessageChannel
    {
        return RabbitMqMessageFanoutChannel::withLogger($logger);
    }

}