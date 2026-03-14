<?php

declare(strict_types=1);

namespace Sudoku\Solving\Eliminator;

use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\EliminatorInterface;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ValueObject\EliminationEntry;

final class XWingEliminator implements EliminatorInterface
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
                if (count($cols1) !== 2) {
                    continue;
                }

                for ($r2 = $r1 + 1; $r2 < 9; $r2++) {
                    $cols2 = $this->colsWithDigit($sudoku, $r2, $digit);
                    if ($cols1 !== $cols2) {
                        continue;
                    }

                    foreach ($cols1 as $col) {
                        for ($r = 0; $r < 9; $r++) {
                            if ($r === $r1 || $r === $r2) {
                                continue;
                            }
                            $cell = $sudoku->getRow($r)[$col];
                            if ($cell->isEmpty() && in_array($digit, $cell->getCandidates(), true)) {
                                $cell->removeCandidate($digit);
                                $entries[] = new EliminationEntry(new Coordinate($r, $col), $digit, Technique::XWing);
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
                if (count($rows1) !== 2) {
                    continue;
                }

                for ($c2 = $c1 + 1; $c2 < 9; $c2++) {
                    $rows2 = $this->rowsWithDigit($sudoku, $c2, $digit);
                    if ($rows1 !== $rows2) {
                        continue;
                    }

                    foreach ($rows1 as $row) {
                        for ($c = 0; $c < 9; $c++) {
                            if ($c === $c1 || $c === $c2) {
                                continue;
                            }
                            $cell = $sudoku->getRow($row)[$c];
                            if ($cell->isEmpty() && in_array($digit, $cell->getCandidates(), true)) {
                                $cell->removeCandidate($digit);
                                $entries[] = new EliminationEntry(new Coordinate($row, $c), $digit, Technique::XWing);
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
