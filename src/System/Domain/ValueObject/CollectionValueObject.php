<?php

declare(strict_types=1);

namespace App\System\Domain\ValueObject;

use App\System\Domain\Service\JsonSerializer;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements \Iterator<TKey, TValue>
 */
class CollectionValueObject implements \Iterator, \Countable, \JsonSerializable, \Stringable
{
    /** @var array<TKey, TValue> */
    private array $items;

    /**
     * @param array<TKey, TValue> $items
     */
    final private function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @param array<TKey, TValue> $items
     * @return static(CollectionValueObject<TKey, TValue>)
     */
    public static function from(array $items): static
    {
        return new static($items);
    }

    /**
     * @return TValue|false
     */
    public function current(): mixed
    {
        return \current($this->items);
    }

    public function next(): void
    {
        \next($this->items);
    }

    /**
     * @return TKey|null
     */
    public function key(): string|int|null
    {
        return \key($this->items);
    }

    public function valid(): bool
    {
        $key = $this->key();
        return null !== $key && \array_key_exists($key, $this->items);
    }

    public function rewind(): void
    {
        \reset($this->items);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return \count($this->items);
    }

    /**
     * @param callable(TValue): bool $func
     * @return static(CollectionValueObject<TKey, TValue>)
     */
    public function filter(callable $func): static
    {
        return static::from(\array_values(\array_filter($this->items, $func)));
    }

    /**
     * @param callable(TValue): mixed $func
     * @return static(CollectionValueObject<TKey, TValue>)
     */
    public function map(callable $func): static
    {
        return static::from(\array_map($func, $this->items));
    }

    /**
     * @param callable(TValue, TValue): int $func
     * @return static(CollectionValueObject<TKey, TValue>)
     */
    public function sort(callable $func): static
    {
        $items = $this->items;
        \usort($items, $func);

        return static::from($items);
    }

    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    /**
     * @return array<TKey, TValue>
     */
    final public function jsonSerialize(): array
    {
        return $this->items;
    }

    /**
     * @return TValue|null
     */
    public function first()
    {
        return $this->items[array_key_first($this->items)] ?? null;
    }

    /**
     * @param TValue $item
     * @return static(CollectionValueObject<TKey, TValue>)
     */
    protected function addItem($item): static
    {
        $items = $this->items;
        $items[] = $item;

        return static::from($items);
    }

    /**
     * @param TValue $item
     * @return static(CollectionValueObject<TKey, TValue>)
     */
    protected function removeItem($item): static
    {
        return $this->filter(
            static fn ($current) => $current !== $item,
        );
    }

    /**
     * @return array<TKey, TValue>
     */
    public function value(): array
    {
        return $this->items;
    }

    public function __toString(): string
    {
        return JsonSerializer::encode($this->jsonSerialize());
    }

    /**
     * @param static<TKey, TValue> $other
     */
    public function equalTo(CollectionValueObject $other): bool
    {
        if (static::class !== \get_class($other)) {
            return false;
        }

        $arr1 = $this->items;
        $arr2 = $other->items;

        \sort($arr1);
        \sort($arr2);

        return $arr1 == $arr2;
    }
}
