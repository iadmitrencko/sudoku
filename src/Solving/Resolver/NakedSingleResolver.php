<?php

declare(strict_types=1);

namespace Sudoku\Solving\Resolver;

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

    /**
     * @return Coordinate[]
     */
    public function resolve(Sudoku $sudoku): array
    {
        $resolved = [];

        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $cell = $sudoku->getRow($row)[$col];

                if (!$cell->isEmpty()) {
                    continue;
                }

                $candidates = $cell->getCandidates();

                if (count($candidates) === 1) {
                    $cell->setValue(current($candidates));
                    $resolved[] = new Coordinate($row, $col);
                }
            }
        }

        return $resolved;
    }
}
