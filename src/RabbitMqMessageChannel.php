<?php


namespace Lukasz93P\AsyncMessageChannel;


use ErrorException;
use Lukasz93P\AsyncMessageChannel\exceptions\MessagePublishingFailed;
use Lukasz93P\AsyncMessageChannel\exceptions\MultipleMessagesPublishingFailed;
use Lukasz93P\AsyncMessageChannel\exceptions\OneMessagePublishingFailed;
use Lukasz93P\AsyncMessageChannel\exceptions\ProcessingOfAsynchronousMessageFailed;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Throwable;

class RabbitMqMessageChannel implements AsynchronousMessageChannel
{
    /**
     * @var string
     */
    private $exchangeName;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public static function withLogger(LoggerInterface $logger): self
    {
        return new self($logger);
    }

    private function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->exchangeName = getenv('RABBIT_MQ_EXCHANGE_NAME') ?: 'my_fanout';
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    private function setUp(): void
    {
        static $isSetUp = false;
        if ($isSetUp) {
            return;
        }
        $this->createChannel();
        $this->setHandlerForFailureMassagePublication();
        $isSetUp = true;
    }

    private function createChannel(): void
    {
        $this->channel = (new AMQPStreamConnection(
            getenv('RABBIT_MQ_HOST') ?: 'localhost',
            getenv('RABBIT_MQ_PORT') ?: 5672,
            getenv('RABBIT_MQ_USER') ?: 'guest',
            getenv('RABBIT_MQ_PASSWORD') ?: 'guest'
        ))->channel();
    }

    private function setHandlerForFailureMassagePublication(): void
    {
        $this->channel->set_nack_handler(
            function (AMQPMessage $message) {
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
        $this->setUp();
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
            $this->channel->batch_basic_publish($this->toAMQPMessage($message), $this->exchangeName, $message->routingKey(), true);
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
     * @throws ErrorException
     */
    public function startProcessingQueue(MessageHandler $messageHandler, string $queueName): void
    {
        $this->setUp();
        $this->channel->basic_consume(
            $queueName,
            false,
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($messageHandler) {
                try {
                    $messageHandler->handle(BasicMessage::fromBody($message->getBody()));
                    $this->notifyChannelAboutSuccessfulMessageProcessing($message);
                } catch (Throwable $throwable) {
                    $this->logger->critical(
                        ProcessingOfAsynchronousMessageFailed::fromMessageBodyAndReason($message->getBody(), $throwable)->getMessage()
                    );
                    $this->notifyChannelAboutFailureDuringMessageProcessing($message);
                }
            }
        );

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    private function notifyChannelAboutSuccessfulMessageProcessing(AMQPMessage $message): void
    {
        $this->getChannelOfDeliveredMessage($message)->basic_ack($this->getDeliveryTagOfDeliveredMessage($message));
    }

    private function notifyChannelAboutFailureDuringMessageProcessing(AMQPMessage $message): void
    {
        $this->getChannelOfDeliveredMessage($message)->basic_nack($this->getDeliveryTagOfDeliveredMessage($message), false, true);
    }

    private function getChannelOfDeliveredMessage(AMQPMessage $message): AMQPChannel
    {
        /** @var AMQPChannel $messageChannel */
        $messageChannel = $message->delivery_info['channel'];

        return $messageChannel;
    }

    private function getDeliveryTagOfDeliveredMessage(AMQPMessage $message): string
    {
        return $message->delivery_info['delivery_tag'] ?? '';
    }

}