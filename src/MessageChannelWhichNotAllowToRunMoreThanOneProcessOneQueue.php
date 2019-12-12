<?php
declare(strict_types=1);


namespace Lukasz93P\AsyncMessageChannel;


use Lukasz93P\AsyncMessageChannel\exceptions\QueueIsAlreadyProcessing;
use Psr\Log\LoggerInterface;

class MessageChannelWhichNotAllowToRunMoreThanOneProcessOneQueue implements AsynchronousMessageChannel
{
    /**
     * @var AsynchronousMessageChannel
     */
    private $decoratedAsynchronousMessageChannel;

    public static function decoratorForAsynchronousMessageChannel(AsynchronousMessageChannel $asynchronousMessageChannel): self
    {
        return new self($asynchronousMessageChannel);
    }

    private function __construct(AsynchronousMessageChannel $decoratedAsynchronousMessageChannel)
    {
        $this->decoratedAsynchronousMessageChannel = $decoratedAsynchronousMessageChannel;
    }

    public function add(array $messages): void
    {
        $this->decoratedAsynchronousMessageChannel->add($messages);
    }

    public function startProcessingQueue(MessageHandler $messageHandler, string $queueName, int $maxRunningTimeInSeconds = 0): void
    {
        $filePath = $this->generateFilePath($queueName);
        if (file_exists($filePath)) {
            throw QueueIsAlreadyProcessing::fromQueueName($queueName);
        }
        $file = fopen($filePath, 'wb');
        try {
            fclose($file);
            $this->decoratedAsynchronousMessageChannel->startProcessingQueue($messageHandler, $queueName, $maxRunningTimeInSeconds);
        } finally {
            unlink($filePath);
        }
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->decoratedAsynchronousMessageChannel->setLogger($logger);
    }

    private function generateFilePath(string $queueName): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . $queueName;
    }

}