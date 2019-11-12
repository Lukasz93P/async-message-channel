<?php


namespace Lukasz93P\AsyncMessageChannel;


interface PublishableMessage extends ProcessableMessage
{
    public function routingKey(): string;
}