<?php

declare(strict_types=1);

namespace App\System\Application\Query;

final readonly class SearchResponse implements \JsonSerializable
{
    private function __construct(
        private array $items,
        private int $total,
        private ?int $page,
        private ?int $itemsPerPage,
    ) {
    }

    public static function create(
        array $items,
        int $total,
        ?int $page,
        ?int $itemsPerPage,
    ): self {
        return new self($items, $total, $page, $itemsPerPage);
    }

    public function items(): array
    {
        return $this->items;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function page(): ?int
    {
        return $this->page;
    }

    public function itemsPerPage(): ?int
    {
        return $this->itemsPerPage;
    }

    public function totalPages(): int
    {
        if ($this->itemsPerPage === 0 || $this->itemsPerPage === null) {
            return 0;
        }

        return (int) ceil($this->total / $this->itemsPerPage);
    }

    public function jsonSerialize(): array
    {
        return [
            'items' => $this->items,
            'total' => $this->total,
            'page' => $this->page,
            'items_per_page' => $this->itemsPerPage,
            'total_pages' => $this->totalPages(),
        ];
    }
}

