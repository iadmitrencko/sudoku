<?php

declare(strict_types=1);

namespace Sudoku\Solving\Eliminator;

use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\EliminatorInterface;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ValueObject\EliminationEntry;

final class TwoStringKiteEliminator implements EliminatorInterface
{
    public function getPriority(): int
    {
        return 23;
    }

    /**
     * @return EliminationEntry[]
     */
    public function eliminate(Sudoku $sudoku): array
    {
        $entries = [];

        for ($digit = 1; $digit <= 9; $digit++) {
            for ($r = 0; $r < 9; $r++) {
                $rowCols = $this->colsWithDigit($sudoku, $r, $digit);
                if (count($rowCols) !== 2) {
                    continue;
                }

                foreach ($rowCols as $c) {
                    $colRows = $this->rowsWithDigit($sudoku, $c, $digit);
                    if (count($colRows) !== 2) {
                        continue;
                    }

                    $r2 = current(array_diff($colRows, [$r]));
                    $c2 = current(array_diff($rowCols, [$c]));

                    // Fourth cell (r2, c2) must contain the digit to form a kite
                    $fourthCell = $sudoku->getRow($r2)[$c2];
                    if (!$fourthCell->isEmpty() || !in_array($digit, $fourthCell->getCandidates(), true)) {
                        continue;
                    }

                    // Targets: (r2, c) and (r2, c2) — the two bottom vertices
                    $target1 = new Coordinate($r2, $c);
                    $target2 = new Coordinate($r2, $c2);

                    for ($dr = 0; $dr < 9; $dr++) {
                        for ($dc = 0; $dc < 9; $dc++) {
                            $dCoord = new Coordinate($dr, $dc);
                            if ($this->sameCoord($dCoord, $target1) || $this->sameCoord($dCoord, $target2)) {
                                continue;
                            }

                            $cell = $sudoku->getRow($dr)[$dc];
                            if (!$cell->isEmpty()) {
                                continue;
                            }

                            if ($this->sees($dCoord, $target1) && $this->sees($dCoord, $target2)) {
                                if (in_array($digit, $cell->getCandidates(), true)) {
                                    $cell->removeCandidate($digit);
                                    $entries[] = new EliminationEntry($dCoord, $digit, Technique::TwoStringKite);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $entries;
    }

    /**
     * @return int[]
     */
    private function colsWithDigit(Sudoku $sudoku, int $row, int $digit): array
    {
        $cols = [];
        for ($c = 0; $c < 9; $c++) {
            $cell = $sudoku->getRow($row)[$c];
            if ($cell->isEmpty() && in_array($digit, $cell->getCandidates(), true)) {
                $cols[] = $c;
            }
        }

        return $cols;
    }

    /**
     * @return int[]
     */
    private function rowsWithDigit(Sudoku $sudoku, int $col, int $digit): array
    {
        $rows = [];
        for ($r = 0; $r < 9; $r++) {
            $cell = $sudoku->getRow($r)[$col];
            if ($cell->isEmpty() && in_array($digit, $cell->getCandidates(), true)) {
                $rows[] = $r;
            }
        }

        return $rows;
    }

    private function sees(Coordinate $a, Coordinate $b): bool
    {
        if ($this->sameCoord($a, $b)) {
            return false;
        }
        if ($a->getRow() === $b->getRow() || $a->getCol() === $b->getCol()) {
            return true;
        }

        return intdiv($a->getRow(), 3) === intdiv($b->getRow(), 3)
            && intdiv($a->getCol(), 3) === intdiv($b->getCol(), 3);
    }

    private function sameCoord(Coordinate $a, Coordinate $b): bool
    {
        return $a->getRow() === $b->getRow() && $a->getCol() === $b->getCol();
    }
}
