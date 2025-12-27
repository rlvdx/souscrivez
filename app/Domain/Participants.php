<?php

namespace App\Domain;

use Iterator;
use IteratorAggregate;
use Countable;

final readonly class Participants implements IteratorAggregate, Countable
{
    /**
     * @param array<Participant> $participants
     */
    private function __construct(
        private array $participants = []
    ) {}

    public static function fromArray(array $participants): self
    {
        return new self($participants);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function add(Participant $participant): self
    {
        return new self([...$this->participants, $participant]);
    }

    public function count(): int
    {
        return count($this->participants);
    }

    public function getIterator(): Iterator
    {
        yield from $this->participants;
    }
}
