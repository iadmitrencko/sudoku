<?php

declare(strict_types=1);

namespace Sudoku\Solving\Eliminator;

use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\EliminatorInterface;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ValueObject\EliminationEntry;

final class XyWingEliminator implements EliminatorInterface
{
    /**
     * @return EliminationEntry[]
     */
    public function eliminate(Sudoku $sudoku): array
    {
        $bivalue = $this->collectBivalue($sudoku);
        $entries = [];

        foreach ($bivalue as [$pivotCoord, $pivotCandidates]) {
            [$x, $y] = $pivotCandidates;

            foreach ($bivalue as [$bCoord, $bCandidates]) {
                if (!$this->sees($pivotCoord, $bCoord)) {
                    continue;
                }

                $sharedWithPivot = array_intersect($bCandidates, $pivotCandidates);
                if (count($sharedWithPivot) !== 1) {
                    continue;
                }

                $sharedXY = current($sharedWithPivot); // x or y
                $z = current(array_diff($bCandidates, $pivotCandidates));
                $pivotOther = $sharedXY === $x ? $y : $x;

                foreach ($bivalue as [$cCoord, $cCandidates]) {
                    if (!$this->sees($pivotCoord, $cCoord)) {
                        continue;
                    }
                    if ($this->sameCoord($bCoord, $cCoord)) {
                        continue;
                    }

                    $expectedC = [$pivotOther, $z];
                    sort($expectedC);
                    $sortedC = $cCandidates;
                    sort($sortedC);
                    if ($sortedC !== $expectedC) {
                        continue;
                    }

                    // XY-Wing found: eliminate z from cells that see both B and C
                    for ($r = 0; $r < 9; $r++) {
                        for ($c = 0; $c < 9; $c++) {
                            $dCoord = new Coordinate($r, $c);
                            if ($this->sameCoord($dCoord, $bCoord) || $this->sameCoord($dCoord, $cCoord)) {
                                continue;
                            }

                            $cell = $sudoku->getRow($r)[$c];
                            if (!$cell->isEmpty()) {
                                continue;
                            }

                            if ($this->sees($dCoord, $bCoord) && $this->sees($dCoord, $cCoord)) {
                                if (in_array($z, $cell->getCandidates(), true)) {
                                    $cell->removeCandidate($z);
                                    $entries[] = new EliminationEntry($dCoord, $z, Technique::XyWing);
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
     * @return array<array{Coordinate, int[]}>
     */
    private function collectBivalue(Sudoku $sudoku): array
    {
        $result = [];
        for ($r = 0; $r < 9; $r++) {
            for ($c = 0; $c < 9; $c++) {
                $cell = $sudoku->getRow($r)[$c];
                if ($cell->isEmpty() && count($cell->getCandidates()) === 2) {
                    $result[] = [new Coordinate($r, $c), array_values($cell->getCandidates())];
                }
            }
        }

        return $result;
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
