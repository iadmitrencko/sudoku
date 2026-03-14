<?php

declare(strict_types=1);

namespace Sudoku\Solving\ValueObject;

use Sudoku\Base\ValueObject\Sudoku;

final class SolvingResult
{
    /**
     * @param array<ResolvedCell|EliminationEntry> $steps
     */
    public function __construct(
        private readonly Sudoku $sudoku,
        private readonly array $steps,
    ) {
    }

    public function getSudoku(): Sudoku
    {
        return $this->sudoku;
    }

    /**
     * @return array<ResolvedCell|EliminationEntry>
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * @return ResolvedCell[]
     */
    public function getResolutions(): array
    {
        return array_values(array_filter($this->steps, static fn($s) => $s instanceof ResolvedCell));
    }
}
