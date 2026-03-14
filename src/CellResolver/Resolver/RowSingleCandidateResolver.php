<?php

declare(strict_types=1);

namespace Sudoku\CellResolver\Resolver;

use Sudoku\Base\ValueObject\Cell;
use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\CellResolver\Enum\Technique;
use Sudoku\CellResolver\ResolverInterface;
use Sudoku\CellResolver\ValueObject\ResolutionLog;
use Sudoku\CellResolver\ValueObject\ResolvedCell;

final class RowSingleCandidateResolver implements ResolverInterface
{
    public function getTechnique(): Technique
    {
        return Technique::RowSingleCandidate;
    }

    public function resolve(Sudoku $sudoku, ResolutionLog $log): void
    {
        for ($row = 0; $row < 9; $row++) {
            $cells = $sudoku->getRow($row);
            $emptyCells = array_filter($cells, static fn(Cell $cell) => $cell->isEmpty());

            if (count($emptyCells) !== 1) {
                continue;
            }

            $col = array_key_first($emptyCells);
            $value = $this->findMissingValue($cells);

            $emptyCells[$col]->setValue($value);
            $log->add(new ResolvedCell(new Coordinate($row, $col), Technique::RowSingleCandidate));
        }
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
