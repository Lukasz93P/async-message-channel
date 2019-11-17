<?php
declare(strict_types=1);


namespace Lukasz93P\AsyncMessageChannel\exceptions;


use Throwable;

class MessageTemporaryUnprocessable extends MessageUnprocessable
{
    protected function generateMessage(Throwable $reason): string
    {
        return 'Message  has been marked as temporary unprocessable that means it should be delivered again until successful processing. Reason:'
            . PHP_EOL . $reason->getMessage();
    }

}