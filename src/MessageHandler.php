<?php


namespace Lukasz93P\AsyncMessageChannel;


use Throwable;

interface MessageHandler
{
    /**
     * @param ProcessableMessage $message
     * @throws Throwable
     */
    public function handle(ProcessableMessage $message): void;
}