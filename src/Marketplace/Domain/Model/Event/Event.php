<?php

declare(strict_types=1);

namespace App\Marketplace\Domain\Model\Event;

use App\Marketplace\Domain\Model\Event\Event\EventWasCreated;
use App\Marketplace\Domain\Model\Event\Event\EventWasUpdated;
use App\Marketplace\Domain\Model\Event\ValueObject\Id;
use App\System\Domain\Model\Aggregate;

class Event extends Aggregate
{
    private const string NAME = 'event';

    private function __construct(
        private readonly Id $id,
    ) {
        parent::__construct();
    }

    public static function from(
        string $id,
    ): self {
        return new self(
            Id::from($id),
        );
    }

    public static function create(
        string $id,
    ): self {
        $user = new self(
            $idVo = Id::from($id),
        );

        $user->recordThat(
            EventWasCreated::from($idVo)
        );

        return $user;
    }

    public function update(): self
    {

        $user = new self(
            $this->id,
        );

        $user->recordThat(
            EventWasUpdated::from($this->id)
        );

        return $user;
    }

    public function id(): Id
    {
        return $this->id;
    }

    public static function modelName(): string
    {
        return self::NAME;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
