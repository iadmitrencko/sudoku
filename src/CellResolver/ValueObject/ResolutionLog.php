<?php

declare(strict_types=1);

namespace Sudoku\CellResolver\ValueObject;

final class ResolutionLog
{
    /** @var ResolutionLogEntry[] */
    private array $entries = [];

    private int $sequence = 0;

    public function add(ResolvedCell $cell): void
    {
        $this->entries[] = new ResolutionLogEntry(++$this->sequence, $cell);
    }

    /** @return ResolutionLogEntry[] */
    public function getEntries(): array
    {
        return $this->entries;
    }

    public function count(): int
    {
        return $this->sequence;
    }
}
