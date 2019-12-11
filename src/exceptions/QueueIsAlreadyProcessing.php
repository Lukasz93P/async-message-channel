<?php
declare(strict_types=1);


namespace Lukasz93P\AsyncMessageChannel\exceptions;


use Throwable;

class QueueIsAlreadyProcessing extends MessageChannelException
{
    public static function fromQueueName(string $queueName): self
    {
        return new self("Queue $queueName is already processing.");
    }

    private function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}