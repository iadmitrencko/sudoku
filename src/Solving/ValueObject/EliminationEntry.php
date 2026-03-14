<?php

declare(strict_types=1);

namespace Sudoku\Solving\ValueObject;

use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Solving\Enum\Technique;

final class EliminationEntry
{
    public function __construct(
        private readonly Coordinate $coordinate,
        private readonly int $value,
        private readonly Technique $technique,
    ) {
    }

    public function getCoordinate(): Coordinate
    {
        return $this->coordinate;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getTechnique(): Technique
    {
        return $this->technique;
    }
}
