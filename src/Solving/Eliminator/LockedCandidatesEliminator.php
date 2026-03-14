<?php

declare(strict_types=1);

namespace Sudoku\Solving\Eliminator;

use Sudoku\Base\Exception\InvalidCoordinateException;
use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\EliminatorInterface;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ValueObject\EliminationEntry;

final class LockedCandidatesEliminator implements EliminatorInterface
{
    public function eliminate(Sudoku $sudoku): array
    {
        return array_merge(
            $this->pointing($sudoku),
            $this->claiming($sudoku),
        );
    }

    /**
     * @return EliminationEntry[]
     *
     * @throws InvalidCoordinateException
     */
    private function pointing(Sudoku $sudoku): array
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
                            $entries[] = new EliminationEntry(
                                new Coordinate($r, $col),
                                $num,
                                Technique::LockedCandidatesPointing,
                            );
                        }
                    }
                }
            }
        }

        return $entries;
    }

    /**
     * @return EliminationEntry[]
     *
     * @throws InvalidCoordinateException
     */
    private function claiming(Sudoku $sudoku): array
    {
        $entries = [];

        for ($row = 0; $row < 9; $row++) {
            for ($num = 1; $num <= 9; $num++) {
                $cols = [];
                for ($col = 0; $col < 9; $col++) {
                    $cell = $sudoku->getRow($row)[$col];
                    if ($cell->isEmpty() && in_array($num, $cell->getCandidates(), true)) {
                        $cols[] = $col;
                    }
                }

                if (count($cols) < 2) {
                    continue;
                }

                $blocks = array_unique(array_map(static fn(int $c) => intdiv($c, 3), $cols));
                if (count($blocks) !== 1) {
                    continue;
                }

                $startCol = $blocks[0] * 3;
                $startRow = intdiv($row, 3) * 3;

                for ($r = $startRow; $r < $startRow + 3; $r++) {
                    if ($r === $row) {
                        continue;
                    }
                    for ($c = $startCol; $c < $startCol + 3; $c++) {
                        $cell = $sudoku->getRow($r)[$c];
                        if ($cell->isEmpty() && in_array($num, $cell->getCandidates(), true)) {
                            $cell->removeCandidate($num);
                            $entries[] = new EliminationEntry(new Coordinate($r, $c), $num, Technique::LockedCandidatesClaiming);
                        }
                    }
                }
            }
        }

        for ($col = 0; $col < 9; $col++) {
            for ($num = 1; $num <= 9; $num++) {
                $rows = [];
                for ($row = 0; $row < 9; $row++) {
                    $cell = $sudoku->getRow($row)[$col];
                    if ($cell->isEmpty() && in_array($num, $cell->getCandidates(), true)) {
                        $rows[] = $row;
                    }
                }

                if (count($rows) < 2) {
                    continue;
                }

                $blocks = array_unique(array_map(static fn(int $r) => intdiv($r, 3), $rows));
                if (count($blocks) !== 1) {
                    continue;
                }

                $startRow = $blocks[0] * 3;
                $startCol = intdiv($col, 3) * 3;

                for ($c = $startCol; $c < $startCol + 3; $c++) {
                    if ($c === $col) {
                        continue;
                    }
                    for ($r = $startRow; $r < $startRow + 3; $r++) {
                        $cell = $sudoku->getRow($r)[$c];
                        if ($cell->isEmpty() && in_array($num, $cell->getCandidates(), true)) {
                            $cell->removeCandidate($num);
                            $entries[] = new EliminationEntry(new Coordinate($r, $c), $num, Technique::LockedCandidatesClaiming);
                        }
                    }
                }
            }
        }

        return $entries;
    }
}
