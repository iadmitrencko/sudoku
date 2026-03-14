<?php

declare(strict_types=1);

namespace Sudoku\Solving;

use Sudoku\Base\ValueObject\Sudoku;

interface EliminatorInterface
{
    public function eliminate(Sudoku $sudoku): bool;
}
