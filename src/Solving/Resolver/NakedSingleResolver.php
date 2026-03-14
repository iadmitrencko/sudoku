<?php

declare(strict_types=1);

namespace Sudoku\Solving\Resolver;

use Sudoku\Base\ValueObject\Cell;
use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ResolverInterface;

final class NakedSingleResolver implements ResolverInterface
{
    public function getTechnique(): Technique
    {
        return Technique::NakedSingle;
    }

    public function getPriority(): int
    {
        return 4;
    }

    public function resolve(Sudoku $sudoku): array
    {
        $resolved = [];

        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $cell = $sudoku->getRow($row)[$col];

                if (!$cell->isEmpty()) {
                    continue;
                }

                $used = $this->collectUsed($sudoku, $row, $col);
                $candidates = array_diff(range(1, 9), $used);

                if (count($candidates) === 1) {
                    $cell->setValue(current($candidates));
                    $resolved[] = new Coordinate($row, $col);
                }
            }
        }

        return $resolved;
    }

    /**
     * @return int[]
     */
    private function collectUsed(Sudoku $sudoku, int $row, int $col): array
    {
        $block = intdiv($row, 3) * 3 + intdiv($col, 3);

        $values = array_merge(
            array_map(static fn(Cell $c) => $c->getValue(), $sudoku->getRow($row)),
            array_map(static fn(Cell $c) => $c->getValue(), $sudoku->getCol($col)),
            array_map(static fn(Cell $c) => $c->getValue(), $sudoku->getBlock($block)),
        );

        return array_values(array_unique(array_filter($values)));
    }
}
