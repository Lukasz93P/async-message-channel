<?php
declare(strict_types=1);


namespace Lukasz93P\AsyncMessageChannel\exceptions;


use Throwable;

class MessageConstantlyUnprocessable extends MessageUnprocessable
{
    protected function generateMessage(Throwable $reason): string
    {
        return 'Message  has been marked as constantly unprocessable a it should not be delivered again. Reason:' . PHP_EOL . $reason->getMessage();
    }

}