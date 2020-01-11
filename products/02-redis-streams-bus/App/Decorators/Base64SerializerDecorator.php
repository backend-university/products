<?php

namespace App\Decorators;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class Base64SerializerDecorator implements SerializerInterface
{
    /** @var SerializerInterface */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function encode(Envelope $envelope): array
    {
        $encoded = $this->serializer->encode($envelope);
        foreach ($encoded as $key => $value) {
            $encoded[$key] = \base64_encode($value);
        }

        return $encoded;
    }

    public function decode(array $encodedEnvelope): Envelope
    {
        foreach ($encodedEnvelope as $key => $value) {
            $encodedEnvelope[$key] = \is_string($value) ? \base64_decode($value) : $value;
        }

        return $this->serializer->decode($encodedEnvelope);
    }
}
