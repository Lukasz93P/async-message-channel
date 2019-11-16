# async-message-channel

##### Current implementation uses RabbitMQ, so to utilize it You have to install this broker.

## Why to use this package? ##
- It abstracts all difficulties associated with asynchronous messages publication and processing.
- It guarantees reliable publication of messages - in case of failure in publication even one of the messages
exception is thrown.
- It helps with passing unsuccessfully processed messages back to queue.

## How to use

### Publishing

##### 1 Add env configuration:
```.dotenv
MQ_BROKER_HOST={rabbit host}
MQ_BROKER_PORT={rabbit port}
MQ_BROKER_USER={rabbit user}
MQ_BROKER_PASSWORD={rabbit password}
```

##### 2 Create instance of ```AsynchronousMessageChannel```:
```php
$asynchronousMessageChannel = AsynchronousMessageChannelFactory::withLogger($implementationOfPsrLoggerInterface);
```
Logger is instance of class implementing ```Psr\Log\LoggerInterface```, it will be used to log error which 
can occur during message processing.

##### 3 Publish message:
Message have to be instance of class implementing ```PublishableMessage```. Currently implementation is aligned 
with RabbitMQ requirements so ```PublishableMessage``` defines three methods:
- ```body``` - returns body of a message as string
- ```routingKey``` - returns routing key which will be used by RabbitMQ to route message into proper queues
- ```exchangeName``` - returns RabbitMQ's exchange name to use for message publishing

You can use default implementation:
```php
$asynchronousMessageChannel = AsynchronousMessageChannelFactory::withLogger($implementationOfPsrLoggerInterface);
$publishableMessage = BasicMessage::publishable($routingKeyForMessage, $exchangeNameForMessage, $messageBody);
$asynchronousMessageChannel->add([$publishableMessage]);
```
```AsynchronousMessageChannel::add``` receives array of ```PublishableMessage```s and publish them reliably in batch manner.
If it receives information about publishing failure from RabbitMQ ```MessagePublishingFailed``` is thrown.

### Processing

##### 1 Create ```MessageHandler```:
For messages processing implementation of ```MessageHandler``` is needed, this interface defines only one method:
```php
interface MessageHandler
{
    /**
     * @param ProcessableMessage $message
     * @throws Throwable
     * @throws MessageConstantlyUnprocessable
     * @throws MessageTemporaryUnprocessable
     */
    public function handle(ProcessableMessage $message): void;
}
```
As You can see ```handle``` receives ```ProcessableMessage``` as an only argument. 
```php
interface ProcessableMessage
{
    public function body(): string;
}
``` 

##### 2 Decide what to do with processed messages inside ```MessageHandler::handle```:
Client code can decide what to do with processed message thorough implementation of ```handle``` method :
- if ```handle``` method method throws any exception/throwable(excluding ```MessageConstantlyUnprocessable```)
```AsynchronousMessageChannel``` will ```reject``` message from RabbitMQ. When message is rejected it will be 
deleted from queue but You can configure RabbitMQ to use ```fallback queue``` for that purposes and pass messages from
```falback queue``` back to "normal" queue with some delay.
- if ```handle``` throws ```MessageConstantlyUnprocessable``` then ```AsynchronousMessageChannel``` informs RabbitMQ that
message was processed successfully then RabbitMQ simply deletes the message.

****Summary:****

- All exception thrown by ```MessageHandler::handle``` will be logged.
- If message has been processed successfully ```MessageHandler::handle``` should not throw any exception.
- If message processing failed but You **don't want** to receive that message again and log exception then throw
```MessageConstantlyUnprocessable``` inside ```MessageHandler::handle```.
- If message processing failed but You **want** to receive that message again and log exception throw any
exception inside ```MessageHandler::handle```(You can be more explicit and throw ```MessageTemporaryUnprocessable```).
To receive message again You also have to configure ```fallback queue``` for RabbitMQ.

##### 3 Start processing messages:
To start processing messages You need and instance of ```AsynchronousMessageChannel```:
```php
$asynchronousMessageChannel = AsynchronousMessageChannelFactory::withLogger($implementationOfPsrLoggerInterface);
```
And then You should use ```AsynchronousMessageChannel::startProcessingQueue```:
```php
$asynchronousMessageChannel->startProcessingQueue($myImplementationOfMessageHandler, $nameOfRabbitMQQueueFromWhichMessagesWillBeProcessed);
```



 