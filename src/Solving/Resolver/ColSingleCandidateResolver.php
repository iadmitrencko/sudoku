<?php

declare(strict_types=1);

namespace Sudoku\Solving\Resolver;

use Sudoku\Base\ValueObject\Cell;
use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ResolverInterface;
use Sudoku\Solving\ValueObject\ResolutionLog;
use Sudoku\Solving\ValueObject\ResolvedCell;

final class ColSingleCandidateResolver implements ResolverInterface
{
    public function getTechnique(): Technique
    {
        return Technique::ColSingleCandidate;
    }

    public function resolve(Sudoku $sudoku, ResolutionLog $log): void
    {
        for ($col = 0; $col < 9; $col++) {
            $cells = $sudoku->getCol($col);
            $emptyCells = array_filter($cells, static fn(Cell $cell) => $cell->isEmpty());

            if (count($emptyCells) !== 1) {
                continue;
            }

            $row = array_key_first($emptyCells);
            $value = $this->findMissingValue($cells);

            $emptyCells[$row]->setValue($value);
            $log->add(new ResolvedCell(new Coordinate($row, $col), Technique::ColSingleCandidate));
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
