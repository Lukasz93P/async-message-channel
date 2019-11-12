<?php


namespace Lukasz93P\AsyncMessageChannel\exceptions;


class MessageTemporaryUnprocessable extends MessageUnprocessable
{
    protected function generateMessage(): string
    {
        return 'Message  has been marked as temporary unprocessable that means it should be delivered again until successful processing.';
    }

}