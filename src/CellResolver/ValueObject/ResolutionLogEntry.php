<?php

declare(strict_types=1);

namespace Sudoku\CellResolver\ValueObject;

final class ResolutionLogEntry
{
    public function __construct(
        private readonly int $sequence,
        private readonly ResolvedCell $cell,
    ) {
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function getCell(): ResolvedCell
    {
        return $this->cell;
    }
}
