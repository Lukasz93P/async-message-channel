<?php


namespace Lukasz93P\AsyncMessageChannel;


use Assert\Assertion;

class BasicMessage implements Message
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $body;

    public static function fromIdAndBody(string $id, string $body): Message
    {
        Assertion::notBlank($id);
        Assertion::notBlank($body);

        return new self($id, $body);
    }

    private function __construct(string $id, string $body)
    {
        $this->id = $id;
        $this->body = $body;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function body(): string
    {
        return $this->body;
    }

}