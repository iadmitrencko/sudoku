<?php

declare(strict_types=1);

namespace Sudoku\Solving\ValueObject;

use Sudoku\Base\ValueObject\Sudoku;

final class SolvingResult
{
    /**
     * @param ResolvedCell[] $log
     */
    public function __construct(
        private readonly Sudoku $sudoku,
        private readonly array $log,
    ) {
    }

    public function getSudoku(): Sudoku
    {
        return $this->sudoku;
    }

    /**
     * @return ResolvedCell[]
     */
    public function getLog(): array
    {
        return $this->log;
    }
}
