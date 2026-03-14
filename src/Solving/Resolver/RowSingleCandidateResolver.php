<?php

declare(strict_types=1);

namespace Sudoku\Solving\Resolver;

use Sudoku\Base\ValueObject\Cell;
use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ResolverInterface;

final class RowSingleCandidateResolver implements ResolverInterface
{
    public function getTechnique(): Technique
    {
        return Technique::RowSingleCandidate;
    }

    public function getPriority(): int
    {
        return 10;
    }

    public function resolve(Sudoku $sudoku): array
    {
        $resolved = [];

        for ($row = 0; $row < 9; $row++) {
            $cells = $sudoku->getRow($row);
            $emptyCells = array_filter($cells, static fn(Cell $cell) => $cell->isEmpty());

            if (count($emptyCells) !== 1) {
                continue;
            }

            $col = array_key_first($emptyCells);
            $emptyCells[$col]->setValue($this->findMissingValue($cells));
            $resolved[] = new Coordinate($row, $col);
        }

        return $resolved;
    }

    /**
     * @param Cell[] $cells
     */
    private function findMissingValue(array $cells): int
    {
        $filled = array_filter(array_map(static fn(Cell $cell) => $cell->getValue(), $cells));

        return current(array_diff(range(1, 9), $filled));
    }
}
