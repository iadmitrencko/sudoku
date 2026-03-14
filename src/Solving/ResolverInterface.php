<?php

declare(strict_types=1);

namespace Sudoku\Solving;

use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ValueObject\ResolutionLog;

interface ResolverInterface
{
    public function getTechnique(): Technique;

    public function resolve(Sudoku $sudoku, ResolutionLog $log): void;
}
