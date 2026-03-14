<?php

declare(strict_types=1);

namespace Sudoku\Solving\Eliminator;

use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\EliminatorInterface;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ValueObject\EliminationEntry;

final class HiddenQuadEliminator implements EliminatorInterface
{
    public function getPriority(): int
    {
        return 35;
    }

    /**
     * @return EliminationEntry[]
     */
    public function eliminate(Sudoku $sudoku): array
    {
        $entries = [];

        for ($i = 0; $i < 9; $i++) {
            $rowGroup = array_map(static fn(int $col) => new Coordinate($i, $col), range(0, 8));
            array_push($entries, ...$this->eliminateGroup($sudoku, $rowGroup));

            $colGroup = array_map(static fn(int $row) => new Coordinate($row, $i), range(0, 8));
            array_push($entries, ...$this->eliminateGroup($sudoku, $colGroup));

            $startRow = intdiv($i, 3) * 3;
            $startCol = ($i % 3) * 3;
            $blockGroup = [];
            for ($r = $startRow; $r < $startRow + 3; $r++) {
                for ($c = $startCol; $c < $startCol + 3; $c++) {
                    $blockGroup[] = new Coordinate($r, $c);
                }
            }
            array_push($entries, ...$this->eliminateGroup($sudoku, $blockGroup));
        }

        return $entries;
    }

    /**
     * @param Coordinate[] $group
     * @return EliminationEntry[]
     */
    private function eliminateGroup(Sudoku $sudoku, array $group): array
    {
        $positions = [];
        for ($num = 1; $num <= 9; $num++) {
            foreach ($group as $coord) {
                $cell = $sudoku->getRow($coord->getRow())[$coord->getCol()];
                if ($cell->isEmpty() && in_array($num, $cell->getCandidates(), true)) {
                    $positions[$num][] = $coord;
                }
            }
        }

        $entries = [];

        for ($a = 1; $a <= 9; $a++) {
            $posA = $positions[$a] ?? [];
            if (count($posA) < 2 || count($posA) > 4) {
                continue;
            }

            for ($b = $a + 1; $b <= 9; $b++) {
                $posB = $positions[$b] ?? [];
                if (count($posB) < 2 || count($posB) > 4) {
                    continue;
                }

                for ($c = $b + 1; $c <= 9; $c++) {
                    $posC = $positions[$c] ?? [];
                    if (count($posC) < 2 || count($posC) > 4) {
                        continue;
                    }

                    for ($d = $c + 1; $d <= 9; $d++) {
                        $posD = $positions[$d] ?? [];
                        if (count($posD) < 2 || count($posD) > 4) {
                            continue;
                        }

                        $cells = $this->uniqueCoords(array_merge($posA, $posB, $posC, $posD));

                        if (count($cells) !== 4) {
                            continue;
                        }

                        foreach ($cells as $coord) {
                            $cell = $sudoku->getRow($coord->getRow())[$coord->getCol()];
                            foreach ($cell->getCandidates() as $candidate) {
                                if ($candidate !== $a && $candidate !== $b && $candidate !== $c && $candidate !== $d) {
                                    $cell->removeCandidate($candidate);
                                    $entries[] = new EliminationEntry($coord, $candidate, Technique::HiddenQuad);
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
     * @param Coordinate[] $coords
     * @return Coordinate[]
     */
    private function uniqueCoords(array $coords): array
    {
        $seen = [];
        $result = [];
        foreach ($coords as $coord) {
            $key = $coord->getRow() . ',' . $coord->getCol();
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $result[] = $coord;
            }
        }

        return $result;
    }
}
