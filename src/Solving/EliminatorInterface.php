<?php

declare(strict_types=1);

namespace Sudoku\Solving;

use Sudoku\Base\Exception\InvalidCoordinateException;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\ValueObject\EliminationEntry;

interface EliminatorInterface
{
    public function getPriority(): int;

    /**
     * @return EliminationEntry[]
     *
     * @throws InvalidCoordinateException
     */
    public function eliminate(Sudoku $sudoku): array;
}
