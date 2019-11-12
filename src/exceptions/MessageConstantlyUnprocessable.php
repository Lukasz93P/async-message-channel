<?php


namespace Lukasz93P\AsyncMessageChannel\exceptions;


class MessageConstantlyUnprocessable extends MessageUnprocessable
{
    protected function generateMessage(): string
    {
        return 'Message  has been marked as constantly unprocessable a it should not be delivered again.';
    }

}