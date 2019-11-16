<?php
declare(strict_types=1);


namespace Lukasz93P\AsyncMessageChannel\exceptions;


use Throwable;

class MultipleMessagesPublishingFailed extends MessagePublishingFailed
{
    public static function fromReason(Throwable $reason): self
    {
        return new self('Publishing of one or more messages failed.', $reason->getCode(), $reason);
    }

}