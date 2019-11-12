<?php


namespace Lukasz93P\AsyncMessageChannel;


use Lukasz93P\AsyncMessageChannel\exceptions\MessageConstantlyUnprocessable;
use Lukasz93P\AsyncMessageChannel\exceptions\MessageTemporaryUnprocessable;
use Throwable;

interface MessageHandler
{
    /**
     * @param ProcessableMessage $message
     * @throws Throwable
     * @throws MessageConstantlyUnprocessable
     * @throws MessageTemporaryUnprocessable
     */
    public function handle(ProcessableMessage $message): void;
}