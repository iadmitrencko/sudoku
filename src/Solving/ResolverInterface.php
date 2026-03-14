<?php

declare(strict_types=1);

namespace Sudoku\Solving;

use Sudoku\Base\Exception\InvalidCellValueException;
use Sudoku\Base\Exception\InvalidCoordinateException;
use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\Enum\Technique;

interface ResolverInterface
{
    public function getTechnique(): Technique;

    public function getPriority(): int;

    /**
     * @throws InvalidCellValueException
     * @throws InvalidCoordinateException
     *
     * @return Coordinate[]
     */
    public function resolve(Sudoku $sudoku): array;
}
