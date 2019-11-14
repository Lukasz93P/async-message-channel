<?php


namespace tests;


use Lukasz93P\AsyncMessageChannel\BasicMessage;
use Lukasz93P\AsyncMessageChannel\ProcessableMessage;
use Lukasz93P\AsyncMessageChannel\PublishableMessage;
use PHPUnit\Framework\TestCase;

class BasicMessageTest extends TestCase
{
    public function testShouldBeCreatableAsPublishableMessageInstance(): void
    {
        $publishableMessage = BasicMessage::publishable('test key', 'test', 'test');
        $this->assertInstanceOf(PublishableMessage::class, $publishableMessage);
    }

    public function testShouldBeCreatableAsProcessableMessageInstance(): void
    {
        $processableMessage = BasicMessage::processable('test');
        $this->assertInstanceOf(ProcessableMessage::class, $processableMessage);
    }

    public function testCreatedAsProcessableMessageShouldReturnBodyProvidedDuringCreation(): void
    {
        $body = 'test test smth body test';
        $processableMessage = BasicMessage::processable($body);
        $this->assertEquals($body, $processableMessage->body());
    }

}