<?php
declare(strict_types=1);


namespace Lukasz93P\AsyncMessageChannel\exceptions;


class OneMessagePublishingFailed extends MessagePublishingFailed
{
    public static function fromMessageBody(string $messageBody): self
    {
        return new self('Message publishing failed. Body of message:' . PHP_EOL . $messageBody);
    }

}