<?php

declare(strict_types=1);

namespace Sudoku\CellResolver;

use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\CellResolver\Enum\Technique;
use Sudoku\CellResolver\ValueObject\ResolutionLog;

interface ResolverInterface
{
    public function getTechnique(): Technique;

    public function resolve(Sudoku $sudoku, ResolutionLog $log): void;
}
