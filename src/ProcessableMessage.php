<?php


namespace Lukasz93P\AsyncMessageChannel;


interface ProcessableMessage
{
    public function body(): string;
}