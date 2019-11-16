<?php
declare(strict_types=1);


namespace Lukasz93P\AsyncMessageChannel\exceptions;


use Throwable;

abstract class MessagePublishingFailed extends MessageChannelException
{
    protected function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}