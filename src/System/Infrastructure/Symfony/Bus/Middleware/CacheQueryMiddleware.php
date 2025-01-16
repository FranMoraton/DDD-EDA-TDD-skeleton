<?php

declare(strict_types=1);

namespace App\System\Infrastructure\Symfony\Bus\Middleware;

use App\System\Application\CacheQuery;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class CacheQueryMiddleware implements MiddlewareInterface
{
    private CacheItemPoolInterface $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if ($message instanceof CacheQuery) {
            $cacheKey = $this->getCacheKey($message);

            $cacheItem = $this->cache->getItem($cacheKey);

            if ($cacheItem->isHit() && null !== $cacheItem->get()) {
                $cachedResult = $cacheItem->get();
                return $envelope->with(new HandledStamp($cachedResult, self::class));
            }
        }

        $envelope = $stack->next()->handle($envelope, $stack);

        if ($message instanceof CacheQuery) {
            /** @var HandledStamp|null $handledStamp */
            $handledStamp = $envelope->last(HandledStamp::class);

            if ($handledStamp) {
                $result = $handledStamp->getResult();

                $cacheKey = $this->getCacheKey($message);
                $cacheItem = $this->cache->getItem($cacheKey);
                $cacheItem->set($result);
                $cacheItem->expiresAfter($message->expirationTime());
                $this->cache->save($cacheItem);
            }
        }

        return $envelope;
    }

    private function getCacheKey(CacheQuery $message): string
    {
        return 'query_' . md5(get_class($message) . serialize($message->payload()));
    }
}
