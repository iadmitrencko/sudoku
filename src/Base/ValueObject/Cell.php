<?php

declare(strict_types=1);

namespace Sudoku\Base\ValueObject;

use Sudoku\Base\Exception\InvalidCellValueException;

final class Cell
{
    private ?int $value;

    /**
     * @var int[]
     */
    private array $candidates;

    /**
     * @throws InvalidCellValueException
     */
    public function __construct(?int $value = null)
    {
        if ($value !== null && ($value < 1 || $value > 9)) {
            throw new InvalidCellValueException();
        }

        $this->value = $value;
        $this->candidates = $value !== null ? [] : range(1, 9);
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
        $this->candidates = [];
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return $this->value === null;
    }

    /**
     * @return int[]
     */
    public function getCandidates(): array
    {
        return $this->candidates;
    }

    public function removeCandidate(int $value): void
    {
        $this->candidates = array_diff($this->candidates, [$value]);
    }
}
