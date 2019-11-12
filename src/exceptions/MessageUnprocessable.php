<?php


namespace Lukasz93P\AsyncMessageChannel\exceptions;


use Throwable;

abstract class MessageUnprocessable extends MessageChannelException
{
    public static function fromReason(Throwable $reason): self
    {
        return new static($reason->getCode(), $reason);
    }

    private function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct($this->generateMessage(), $code, $previous);
    }

    abstract protected function generateMessage(): string;
}