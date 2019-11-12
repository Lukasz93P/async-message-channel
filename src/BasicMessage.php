<?php


namespace Lukasz93P\AsyncMessageChannel;


class BasicMessage implements PublishableMessage
{
    /**
     * @var string
     */
    private $routingKey;

    /**
     * @var string
     */
    private $body;

    public static function fromBody(string $body): ProcessableMessage
    {
        return new self('', $body);
    }

    public static function fromRoutingKeyAndBody(string $id, string $body): PublishableMessage
    {
        return new self($id, $body);
    }

    private function __construct(string $routingKey, string $body)
    {
        $this->routingKey = $routingKey;
        $this->body = $body;
    }

    public function routingKey(): string
    {
        return $this->routingKey;
    }

    public function body(): string
    {
        return $this->body;
    }

}