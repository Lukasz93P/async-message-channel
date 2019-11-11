<?php


namespace Lukasz93P\AsyncMessageChannel;


use Throwable;

interface MessageHandler
{
    /**
     * @param Message $message
     * @throws Throwable
     */
    public function handle(Message $message): void;
}