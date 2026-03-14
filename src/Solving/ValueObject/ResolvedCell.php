<?php

declare(strict_types=1);

namespace Sudoku\Solving\ValueObject;

use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Solving\Enum\Technique;

final class ResolvedCell
{
    public function __construct(
        private readonly Coordinate $coordinate,
        private readonly Technique $technique,
        private readonly int $value,
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

    public function getValue(): int
    {
        return $this->value;
    }
}
