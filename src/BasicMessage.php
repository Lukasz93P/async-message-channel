<?php
declare(strict_types=1);


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
    private $exchange;

    /**
     * @var string
     */
    private $body;

    public static function processable(string $body): ProcessableMessage
    {
        return new self('', '', $body);
    }

    public static function publishable(string $routingKey, string $exchange, string $body): PublishableMessage
    {
        return new self($routingKey, $exchange, $body);
    }

    protected function __construct(string $routingKey, string $exchange, string $body)
    {
        $this->routingKey = $routingKey;
        $this->exchange = $exchange;
        $this->body = $body;
    }

    public function routingKey(): string
    {
        return $this->routingKey;
    }

    public function exchange(): string
    {
        return $this->exchange;
    }

    public function body(): string
    {
        return $this->body;
    }

}