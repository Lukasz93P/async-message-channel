<?php
declare(strict_types=1);


namespace Lukasz93P\AsyncMessageChannel\exceptions;


use Throwable;

class ProcessingOfAsynchronousMessageFailed extends MessageChannelException
{
    public static function fromMessageBodyAndReason(string $messageBody, Throwable $reason): self
    {
        return new self(self::generateExceptionMessage($messageBody, $reason), $reason->getCode(), $reason);
    }

    private static function generateExceptionMessage(string $messageBody, Throwable $reason): string
    {
        return 'Throwable was thrown during processing asynchronous message. Message body:' . PHP_EOL . $messageBody . PHP_EOL . "Reason: {$reason->getMessage()}";
    }

    private function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}