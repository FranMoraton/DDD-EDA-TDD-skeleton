<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\Bus\Serializer;

use App\System\Application\Command;
use App\System\Application\Query;
use App\System\Domain\Event\DomainEvent;
use App\System\Domain\ValueObject\DateTimeValueObject;
use App\System\Domain\ValueObject\Uuid;
use App\System\Infrastructure\Symfony\Bus\Serializer\Mappers\CommandMapper;
use App\System\Infrastructure\Symfony\Bus\Serializer\Mappers\EventMapper;
use App\System\Infrastructure\Symfony\Bus\Serializer\Mappers\QueryMapper;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

final class CustomSerializer implements SerializerInterface
{
    public function __construct(
        private CommandMapper $commandMapper,
        private EventMapper $eventMapper,
        private QueryMapper $queryMapper,
    ){
    }

    /**
     * @param array{body: string, headers: array<string, string>} $encodedEnvelope
     * @throws \DateMalformedStringException
     */
    public function decode(array $encodedEnvelope): Envelope
    {
        $body = \json_decode($encodedEnvelope['body'], true);
        $headers = $encodedEnvelope['headers'];
        $stamps = [];

        if (false === \is_array($body)) {
            throw new MessageDecodingFailedException('Unable to decode message body');
        }

        if (true === \array_key_exists('X-Retry-Count', $headers)
            && true === \array_key_exists('X-Redelivered-At', $headers)
        ) {
            $stamps[] = new RedeliveryStamp(
                (int)$headers['X-Retry-Count'],
                new \DateTimeImmutable($headers['X-Redelivered-At']),
            );
        }

        $this->assertContent($body);
        $messageId = $body['message_id'];
        $type = $body['type'];
        $payload = $body['payload'];

        $className = $this->commandMapper->get($type);

        if (null !== $className) {
            $message = $className::fromPayload(
                Uuid::from($messageId),
                $payload,
            );
            \assert($message instanceof Command);

            return new Envelope($message, $stamps);
        }

        $className = $this->queryMapper->get($type);

        if (null !== $className) {
            $message = $className::fromPayload(
                Uuid::from($messageId),
                $payload,
            );
            \assert($message instanceof Query);

            return new Envelope($message, $stamps);
        }

        $className = $this->eventMapper->get($type);

        if (null === $className) {
            throw new MessageDecodingFailedException(\sprintf('Unable to find message of type <%s>', $type));
        }

        $this->assertEventContent($body);
        $aggregateId = $body['aggregate_id'];
        $occurredOn = $body['occurred_on'];

        $message = $className::fromPayload(
            Uuid::from($messageId),
            Uuid::from($aggregateId),
            DateTimeValueObject::from($occurredOn),
            $payload,
        );
        \assert($message instanceof DomainEvent);

        return new Envelope($message, $stamps);
    }

    public function encode(Envelope $envelope): array
    {
        $message = $this->getMessage($envelope);
        $headers = [
            'Content-Type' => 'application/json',
        ];

        $redeliveryStamp = $envelope->last(RedeliveryStamp::class);

        if (null !== $redeliveryStamp) {
            $headers['X-Retry-Count'] = $redeliveryStamp->getRetryCount();
            $headers['X-Redelivered-At'] = $redeliveryStamp->getRedeliveredAt()->format(\DateTimeInterface::ATOM);
        }

        $body = [
            'message_id' => $message->messageId(),
            'type' => $message::messageName(),
            'payload' => $message->payload(),
        ];

        if ($message instanceof DomainEvent) {
            $body['aggregate_id'] = $message->aggregateId()->value();
            $body['occurred_on'] = $message->occurredOn()->value();
        }

        return [
            'body' => \json_encode($body, \JSON_THROW_ON_ERROR),
            'headers' => $headers,
        ];
    }

    private function getMessage(Envelope $envelope): DomainEvent|Command|Query
    {
        $message = $envelope->getMessage();

        return match (true) {
            $message instanceof DomainEvent => $message,
            $message instanceof Query => $message,
            $message instanceof Command => $message,
            default => throw new MessageDecodingFailedException('Message not supported'),
        };
    }

    private function assertContent(array $content): void
    {
//        Assert::lazy()->tryAll()
//            ->that($content, 'content')->isArray()
//            ->keyExists('message_id')
//            ->keyExists('type')
//            ->keyExists('payload')
//            ->verifyNow();
//
//        Assert::lazy()->tryAll()
//            ->that($content['message_id'], 'message_id')->uuid()
//            ->that($content['type'], 'type')->string()->notEmpty()
//            ->that($content['payload'], 'attributes')->isArray()
//            ->verifyNow();
    }

    private function assertEventContent(array $content): void
    {
//        Assert::lazy()->tryAll()
//            ->that($content, 'content')->isArray()
//            ->keyExists('aggregate_id')
//            ->keyExists('occurred_on')
//            ->verifyNow();
//
//        Assert::lazy()->tryAll()
//            ->that($content['aggregate_id'], 'aggregate_id')->uuid()
//            ->that($content['occurred_on'], 'occurred_on')->date(\DateTimeInterface::RFC3339_EXTENDED)
//            ->verifyNow();
    }
}
