<?php
declare(strict_types=1);


namespace Lukasz93P\AsyncMessageChannel;


use ErrorException;
use Lukasz93P\AsyncMessageChannel\exceptions\MessagePublishingFailed;
use Psr\Log\LoggerAwareInterface;

interface AsynchronousMessageChannel extends LoggerAwareInterface
{
    /**
     * @param PublishableMessage[] $messages
     * @throws MessagePublishingFailed
     */
    public function add(array $messages): void;

    /**
     * @param MessageHandler $messageHandler
     * @param string $queueName
     * @throws ErrorException
     */
    public function startProcessingQueue(MessageHandler $messageHandler, string $queueName): void;
}