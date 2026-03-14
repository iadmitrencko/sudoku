<?php

declare(strict_types=1);

namespace Sudoku\CellResolver\ValueObject;

use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\CellResolver\Enum\Technique;

final class ResolvedCell
{
    public function __construct(
        private readonly Coordinate $coordinate,
        private readonly Technique $technique,
    ) {
    }

    public function getCoordinate(): Coordinate
    {
        return $this->coordinate;
    }

    public function getTechnique(): Technique
    {
        return $this->technique;
    }
}
