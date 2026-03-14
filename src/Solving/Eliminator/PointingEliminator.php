<?php

declare(strict_types=1);

namespace Sudoku\Solving\Eliminator;

use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\EliminatorInterface;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ValueObject\EliminationEntry;

final class PointingEliminator implements EliminatorInterface
{
    /**
     * @return EliminationEntry[]
     */
    public function eliminate(Sudoku $sudoku): array
    {
        $entries = [];

        for ($block = 0; $block < 9; $block++) {
            $startRow = intdiv($block, 3) * 3;
            $startCol = ($block % 3) * 3;

            for ($num = 1; $num <= 9; $num++) {
                $positions = [];

                for ($r = $startRow; $r < $startRow + 3; $r++) {
                    for ($c = $startCol; $c < $startCol + 3; $c++) {
                        $cell = $sudoku->getRow($r)[$c];
                        if ($cell->isEmpty() && in_array($num, $cell->getCandidates(), true)) {
                            $positions[] = [$r, $c];
                        }
                    }
                }

                if (count($positions) < 2) {
                    continue;
                }

                $rows = array_unique(array_column($positions, 0));
                if (count($rows) === 1) {
                    $row = $rows[0];
                    for ($c = 0; $c < 9; $c++) {
                        if ($c >= $startCol && $c < $startCol + 3) {
                            continue;
                        }
                        $cell = $sudoku->getRow($row)[$c];
                        if ($cell->isEmpty() && in_array($num, $cell->getCandidates(), true)) {
                            $cell->removeCandidate($num);
                            $entries[] = new EliminationEntry(new Coordinate($row, $c), $num, Technique::LockedCandidatesPointing);
                        }
                    }
                }

                $cols = array_unique(array_column($positions, 1));
                if (count($cols) === 1) {
                    $col = $cols[0];
                    for ($r = 0; $r < 9; $r++) {
                        if ($r >= $startRow && $r < $startRow + 3) {
                            continue;
                        }
                        $cell = $sudoku->getRow($r)[$col];
                        if ($cell->isEmpty() && in_array($num, $cell->getCandidates(), true)) {
                            $cell->removeCandidate($num);
                            $entries[] = new EliminationEntry(new Coordinate($r, $col), $num, Technique::LockedCandidatesPointing);
                        }
                    }
                }
            }
        }

        return $entries;
    }
}
