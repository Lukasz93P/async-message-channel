<?php
declare(strict_types=1);


namespace Lukasz93P\AsyncMessageChannel;


interface PublishableMessage extends ProcessableMessage
{
    public function routingKey(): string;

    public function exchange(): string;
}