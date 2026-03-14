<?php

declare(strict_types=1);

namespace Sudoku\Base\ValueObject;

use Sudoku\Base\Exception\InvalidCoordinateException;

final class Coordinate
{
    /**
     * @throws InvalidCoordinateException
     */
    public function __construct(
        private readonly int $row,
        private readonly int $col,
    ) {
        if ($row < 0 || $row > 8) {
            throw new InvalidCoordinateException(sprintf('Row must be between 0 and 8, %d given.', $row));
        }

        if ($col < 0 || $col > 8) {
            throw new InvalidCoordinateException(sprintf('Col must be between 0 and 8, %d given.', $col));
        }
    }

    public function getRow(): int
    {
        return $this->row;
    }

    public function getCol(): int
    {
        return $this->col;
    }
}
