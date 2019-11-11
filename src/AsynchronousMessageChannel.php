<?php


namespace Lukasz93P\AsyncMessageChannel;


use ErrorException;
use Lukasz93P\AsyncMessageChannel\exceptions\MessagePublishingFailed;
use Psr\Log\LoggerAwareInterface;

interface AsynchronousMessageChannel extends LoggerAwareInterface
{
    /**
     * @param Message[] $messages
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