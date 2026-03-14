<?php

declare(strict_types=1);

namespace Sudoku\Solving;

use Sudoku\Base\Exception\InvalidCellValueException;
use Sudoku\Base\Exception\InvalidCoordinateException;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\ValueObject\ResolvedCell;
use Sudoku\Solving\ValueObject\SolvingResult;

final class SudokuSolver
{
    /**
     * @param iterable<ResolverInterface> $resolvers
     */
    public function __construct(
        private readonly iterable $resolvers,
    ) {
    }

    /**
     * @throws InvalidCellValueException
     * @throws InvalidCoordinateException
     */
    public function solve(Sudoku $sudoku): SolvingResult
    {
        $log = [];

        do {
            $resolvedCount = 0;

            foreach ($this->resolvers as $resolver) {
                $coordinates = $resolver->resolve($sudoku);

                foreach ($coordinates as $coordinate) {
                    $log[] = new ResolvedCell($coordinate, $resolver->getTechnique());
                    $resolvedCount++;
                }
            }
        } while ($resolvedCount > 0 && !$sudoku->isSolved());

        return new SolvingResult($sudoku, $log);
    }
}
