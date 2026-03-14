<?php

declare(strict_types=1);

namespace Sudoku\Solving\Resolver;

use Sudoku\Base\ValueObject\Cell;
use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ResolverInterface;

final class BlockSingleCandidateResolver implements ResolverInterface
{
    public function getTechnique(): Technique
    {
        return Technique::BlockSingleCandidate;
    }

    public function getPriority(): int
    {
        return 6;
    }

    public function resolve(Sudoku $sudoku): array
    {
        $resolved = [];

        for ($block = 0; $block < 9; $block++) {
            $cells = $sudoku->getBlock($block);
            $emptyCells = array_filter($cells, static fn(Cell $cell) => $cell->isEmpty());

            if (count($emptyCells) !== 1) {
                continue;
            }

            $indexInBlock = array_key_first($emptyCells);
            $startRow = intdiv($block, 3) * 3;
            $startCol = ($block % 3) * 3;
            $row = $startRow + intdiv($indexInBlock, 3);
            $col = $startCol + ($indexInBlock % 3);

            $emptyCells[$indexInBlock]->setValue($this->findMissingValue($cells));
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
