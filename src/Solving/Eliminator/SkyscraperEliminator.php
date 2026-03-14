<?php

declare(strict_types=1);

namespace Sudoku\Solving\Eliminator;

use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\EliminatorInterface;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ValueObject\EliminationEntry;

final class SkyscraperEliminator implements EliminatorInterface
{
    public function getPriority(): int
    {
        return 25;
    }

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
                    if (count($cols2) !== 2) {
                        continue;
                    }

                    $shared = array_values(array_intersect($cols1, $cols2));
                    if (count($shared) !== 1) {
                        continue;
                    }

                    $sharedCol = $shared[0];
                    $top1Col = current(array_diff($cols1, [$sharedCol]));
                    $top2Col = current(array_diff($cols2, [$sharedCol]));

                    $top1 = new Coordinate($r1, $top1Col);
                    $top2 = new Coordinate($r2, $top2Col);

                    for ($r = 0; $r < 9; $r++) {
                        for ($c = 0; $c < 9; $c++) {
                            $dCoord = new Coordinate($r, $c);
                            if ($this->sameCoord($dCoord, $top1) || $this->sameCoord($dCoord, $top2)) {
                                continue;
                            }

                            $cell = $sudoku->getRow($r)[$c];
                            if (!$cell->isEmpty()) {
                                continue;
                            }

                            if ($this->sees($dCoord, $top1) && $this->sees($dCoord, $top2)) {
                                if (in_array($digit, $cell->getCandidates(), true)) {
                                    $cell->removeCandidate($digit);
                                    $entries[] = new EliminationEntry($dCoord, $digit, Technique::Skyscraper);
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
                if (count($rows1) !== 2) {
                    continue;
                }

                for ($c2 = $c1 + 1; $c2 < 9; $c2++) {
                    $rows2 = $this->rowsWithDigit($sudoku, $c2, $digit);
                    if (count($rows2) !== 2) {
                        continue;
                    }

                    $shared = array_values(array_intersect($rows1, $rows2));
                    if (count($shared) !== 1) {
                        continue;
                    }

                    $sharedRow = $shared[0];
                    $top1Row = current(array_diff($rows1, [$sharedRow]));
                    $top2Row = current(array_diff($rows2, [$sharedRow]));

                    $top1 = new Coordinate($top1Row, $c1);
                    $top2 = new Coordinate($top2Row, $c2);

                    for ($r = 0; $r < 9; $r++) {
                        for ($c = 0; $c < 9; $c++) {
                            $dCoord = new Coordinate($r, $c);
                            if ($this->sameCoord($dCoord, $top1) || $this->sameCoord($dCoord, $top2)) {
                                continue;
                            }

                            $cell = $sudoku->getRow($r)[$c];
                            if (!$cell->isEmpty()) {
                                continue;
                            }

                            if ($this->sees($dCoord, $top1) && $this->sees($dCoord, $top2)) {
                                if (in_array($digit, $cell->getCandidates(), true)) {
                                    $cell->removeCandidate($digit);
                                    $entries[] = new EliminationEntry($dCoord, $digit, Technique::Skyscraper);
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
