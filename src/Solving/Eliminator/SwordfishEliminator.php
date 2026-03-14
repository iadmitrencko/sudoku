<?php

declare(strict_types=1);

namespace Sudoku\Solving\Eliminator;

use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\EliminatorInterface;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ValueObject\EliminationEntry;

final class SwordfishEliminator implements EliminatorInterface
{
    /**
     * @return EliminationEntry[]
     */
    public function eliminate(Sudoku $sudoku): array
    {
        return array_merge(
            $this->eliminateByRows($sudoku),
            $this->eliminateByCols($sudoku),
        );
    }

    /**
     * @return EliminationEntry[]
     */
    private function eliminateByRows(Sudoku $sudoku): array
    {
        $entries = [];

        for ($digit = 1; $digit <= 9; $digit++) {
            for ($r1 = 0; $r1 < 9; $r1++) {
                $cols1 = $this->colsWithDigit($sudoku, $r1, $digit);
                if (empty($cols1)) {
                    continue;
                }

                for ($r2 = $r1 + 1; $r2 < 9; $r2++) {
                    $cols2 = $this->colsWithDigit($sudoku, $r2, $digit);
                    if (empty($cols2)) {
                        continue;
                    }

                    for ($r3 = $r2 + 1; $r3 < 9; $r3++) {
                        $cols3 = $this->colsWithDigit($sudoku, $r3, $digit);
                        if (empty($cols3)) {
                            continue;
                        }

                        $allCols = array_unique(array_merge($cols1, $cols2, $cols3));
                        sort($allCols);

                        if (count($allCols) !== 3) {
                            continue;
                        }

                        foreach ($allCols as $col) {
                            for ($r = 0; $r < 9; $r++) {
                                if ($r === $r1 || $r === $r2 || $r === $r3) {
                                    continue;
                                }
                                $cell = $sudoku->getRow($r)[$col];
                                if ($cell->isEmpty() && in_array($digit, $cell->getCandidates(), true)) {
                                    $cell->removeCandidate($digit);
                                    $entries[] = new EliminationEntry(new Coordinate($r, $col), $digit, Technique::Swordfish);
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
     * @return EliminationEntry[]
     */
    private function eliminateByCols(Sudoku $sudoku): array
    {
        $entries = [];

        for ($digit = 1; $digit <= 9; $digit++) {
            for ($c1 = 0; $c1 < 9; $c1++) {
                $rows1 = $this->rowsWithDigit($sudoku, $c1, $digit);
                if (empty($rows1)) {
                    continue;
                }

                for ($c2 = $c1 + 1; $c2 < 9; $c2++) {
                    $rows2 = $this->rowsWithDigit($sudoku, $c2, $digit);
                    if (empty($rows2)) {
                        continue;
                    }

                    for ($c3 = $c2 + 1; $c3 < 9; $c3++) {
                        $rows3 = $this->rowsWithDigit($sudoku, $c3, $digit);
                        if (empty($rows3)) {
                            continue;
                        }

                        $allRows = array_unique(array_merge($rows1, $rows2, $rows3));
                        sort($allRows);

                        if (count($allRows) !== 3) {
                            continue;
                        }

                        foreach ($allRows as $row) {
                            for ($c = 0; $c < 9; $c++) {
                                if ($c === $c1 || $c === $c2 || $c === $c3) {
                                    continue;
                                }
                                $cell = $sudoku->getRow($row)[$c];
                                if ($cell->isEmpty() && in_array($digit, $cell->getCandidates(), true)) {
                                    $cell->removeCandidate($digit);
                                    $entries[] = new EliminationEntry(new Coordinate($row, $c), $digit, Technique::Swordfish);
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
}
