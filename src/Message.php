<?php


namespace Lukasz93P\AsyncMessageChannel;


interface Message
{
    public function id(): string;

    public function body(): string;
}