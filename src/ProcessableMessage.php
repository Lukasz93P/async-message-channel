<?php
declare(strict_types=1);


namespace Lukasz93P\AsyncMessageChannel;


interface ProcessableMessage
{
    public function body(): string;
}