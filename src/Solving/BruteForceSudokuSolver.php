<?php

declare(strict_types=1);

namespace Sudoku\Solving;

use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ValueObject\ResolvedCell;
use Sudoku\Solving\ValueObject\SolvingResult;

final class BruteForceSudokuSolver
{
    public function solve(Sudoku $sudoku): SolvingResult
    {
        $grid = $sudoku->toGrid();

        $this->backtrack($grid);

        $steps = [];
        for ($r = 0; $r < 9; $r++) {
            for ($c = 0; $c < 9; $c++) {
                $cell = $sudoku->getRow($r)[$c];
                if ($cell->isEmpty()) {
                    $value = $grid[$r][$c];
                    $cell->setValue($value);
                    $steps[] = new ResolvedCell(new Coordinate($r, $c), Technique::Backtracking, $value);
                }
            }
        }

        return new SolvingResult($sudoku, $steps);
    }

    /**
     * @param array<int, array<int, int|null>> $grid
     */
    private function backtrack(array &$grid): bool
    {
        for ($r = 0; $r < 9; $r++) {
            for ($c = 0; $c < 9; $c++) {
                if ($grid[$r][$c] !== null) {
                    continue;
                }

                for ($digit = 1; $digit <= 9; $digit++) {
                    if (!$this->isValid($grid, $r, $c, $digit)) {
                        continue;
                    }

                    $grid[$r][$c] = $digit;

                    if ($this->backtrack($grid)) {
                        return true;
                    }

                    $grid[$r][$c] = null;
                }

                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, array<int, int|null>> $grid
     */
    private function isValid(array $grid, int $row, int $col, int $digit): bool
    {
        if (in_array($digit, $grid[$row], true)) {
            return false;
        }

        for ($r = 0; $r < 9; $r++) {
            if ($grid[$r][$col] === $digit) {
                return false;
            }
        }

        $startRow = intdiv($row, 3) * 3;
        $startCol = intdiv($col, 3) * 3;
        for ($r = $startRow; $r < $startRow + 3; $r++) {
            for ($c = $startCol; $c < $startCol + 3; $c++) {
                if ($grid[$r][$c] === $digit) {
                    return false;
                }
            }
        }

        return true;
    }
}
