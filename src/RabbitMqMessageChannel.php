<?php
declare(strict_types=1);


namespace Lukasz93P\AsyncMessageChannel;


use ErrorException;
use Lukasz93P\AsyncMessageChannel\exceptions\MessageConstantlyUnprocessable;
use Lukasz93P\AsyncMessageChannel\exceptions\MessagePublishingFailed;
use Lukasz93P\AsyncMessageChannel\exceptions\MultipleMessagesPublishingFailed;
use Lukasz93P\AsyncMessageChannel\exceptions\OneMessagePublishingFailed;
use Lukasz93P\AsyncMessageChannel\exceptions\ProcessingOfAsynchronousMessageFailed;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Throwable;

class RabbitMqMessageChannel implements AsynchronousMessageChannel
{
    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public static function withLoggerAndChannel(LoggerInterface $logger, AMQPChannel $channel): self
    {
        return new self($logger, $channel);
    }

    private function __construct(LoggerInterface $logger, AMQPChannel $channel)
    {
        $this->logger = $logger;
        $this->channel = $channel;
        $this->setHandlerForFailureMassagePublication();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    private function setHandlerForFailureMassagePublication(): void
    {
        $this->channel->set_nack_handler(
            static function (AMQPMessage $message) {
                throw OneMessagePublishingFailed::fromMessageBody($message->getBody());
            }
        );
    }

    /**
     * @param PublishableMessage[] $messages
     * @throws MessagePublishingFailed
     */
    public function add(array $messages): void
    {
        if (empty($messages)) {
            return;
        }
        try {
            $this->publish($messages);
        } catch (AMQPTimeoutException $AMQPTimeoutException) {
            throw MultipleMessagesPublishingFailed::fromReason($AMQPTimeoutException);
        }
    }

    /**
     * @param PublishableMessage[] $messages
     */
    private function publish(array $messages): void
    {
        $this->channel->confirm_select();
        foreach ($messages as $message) {
            $this->channel->batch_basic_publish($this->toAMQPMessage($message), $message->exchange(), $message->routingKey(), true);
        }
        $this->channel->publish_batch();
        $this->channel->wait_for_pending_acks();
    }

    private function toAMQPMessage(PublishableMessage $message): AMQPMessage
    {
        return new AMQPMessage(
            $message->body(),
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );
    }

    /**
     * @param MessageHandler $messageHandler
     * @param string $queueName
     * @param int $maxRunningTimeInSeconds
     * @throws ErrorException
     */
    public function startProcessingQueue(MessageHandler $messageHandler, string $queueName, int $maxRunningTimeInSeconds = 0): void
    {
        $this->channel->basic_consume(
            $queueName,
            false,
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($messageHandler) {
                try {
                    $messageHandler->handle(BasicMessage::processable($message->getBody()));
                    $this->notifyChannelAboutSuccessfulMessageProcessing($message);
                } catch (MessageConstantlyUnprocessable $messageConstantlyUnprocessable) {
                    $this->logInformationAboutMessageProcessingFailure($message, $messageConstantlyUnprocessable);
                    $this->notifyChannelAboutSuccessfulMessageProcessing($message);
                } catch (Throwable $throwable) {
                    $this->logInformationAboutMessageProcessingFailure($message, $throwable);
                    $this->rejectTemporaryUnprocessableMessage($message);
                }
            }
        );

        try {
            $start = microtime(true);
            while ($this->channel->is_consuming()) {
                $duration = microtime(true) - $start;
                $channelWaitingTimeout = ($maxRunningTimeInSeconds - $duration) ?: 1;
                if ($maxRunningTimeInSeconds && $channelWaitingTimeout <= 1) {
                    break;
                }
                $this->channel->wait(null, false, $maxRunningTimeInSeconds ? $channelWaitingTimeout : 0);
            }
        } catch (AMQPTimeoutException $exception) {
            return;
        }
    }

    private function notifyChannelAboutSuccessfulMessageProcessing(AMQPMessage $message): void
    {
        $this->getChannelOfDeliveredMessage($message)->basic_ack($this->getDeliveryTagOfDeliveredMessage($message));
    }

    private function rejectTemporaryUnprocessableMessage(AMQPMessage $message): void
    {
        $this->getChannelOfDeliveredMessage($message)->basic_reject($this->getDeliveryTagOfDeliveredMessage($message), false);
    }

    private function logInformationAboutMessageProcessingFailure(AMQPMessage $message, Throwable $reason): void
    {
        $this->logger->error(ProcessingOfAsynchronousMessageFailed::fromMessageBodyAndReason($message->getBody(), $reason)->getMessage());
    }

    private function getChannelOfDeliveredMessage(AMQPMessage $message): AMQPChannel
    {
        /** @var AMQPChannel $messageChannel */
        $messageChannel = $message->delivery_info['channel'];

        return $messageChannel;
    }

    private function getDeliveryTagOfDeliveredMessage(AMQPMessage $message): string
    {
        return (string)($message->delivery_info['delivery_tag'] ?? '');
    }

}