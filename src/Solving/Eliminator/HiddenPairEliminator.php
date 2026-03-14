<?php

declare(strict_types=1);

namespace Sudoku\Solving\Eliminator;

use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\EliminatorInterface;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ValueObject\EliminationEntry;

final class HiddenPairEliminator implements EliminatorInterface
{
    public function getPriority(): int
    {
        return 60;
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
        // For each number, find which cells in the group can contain it
        $positions = []; // num => Coordinate[]
        for ($num = 1; $num <= 9; $num++) {
            foreach ($group as $coord) {
                $cell = $sudoku->getRow($coord->getRow())[$coord->getCol()];
                if ($cell->isEmpty() && in_array($num, $cell->getCandidates(), true)) {
                    $positions[$num][] = $coord;
                }
            }
        }

        // Find pairs of numbers that share exactly the same 2 cells
        $entries = [];
        $checked = [];

        foreach ($positions as $numA => $coordsA) {
            if (count($coordsA) !== 2) {
                continue;
            }

            foreach ($positions as $numB => $coordsB) {
                if ($numB <= $numA) {
                    continue;
                }

                if (count($coordsB) !== 2) {
                    continue;
                }

                $key = $this->coordsKey($coordsA[0], $coordsA[1]);
                if (!$this->samePositions($coordsA, $coordsB)) {
                    continue;
                }

                if (isset($checked[$key])) {
                    continue;
                }
                $checked[$key] = true;

                // Hidden pair found: numA and numB must be in coordsA[0] and coordsA[1]
                // Remove all other candidates from these two cells
                foreach ($coordsA as $coord) {
                    $cell = $sudoku->getRow($coord->getRow())[$coord->getCol()];
                    foreach ($cell->getCandidates() as $candidate) {
                        if ($candidate !== $numA && $candidate !== $numB) {
                            $cell->removeCandidate($candidate);
                            $entries[] = new EliminationEntry($coord, $candidate, Technique::HiddenPair);
                        }
                    }
                }
            }
        }

        return $entries;
    }

    /**
     * @param Coordinate[] $a
     * @param Coordinate[] $b
     */
    private function samePositions(array $a, array $b): bool
    {
        return $this->coordsKey($a[0], $a[1]) === $this->coordsKey($b[0], $b[1]);
    }

    private function coordsKey(Coordinate $first, Coordinate $second): string
    {
        $coords = [
            [$first->getRow(), $first->getCol()],
            [$second->getRow(), $second->getCol()],
        ];
        sort($coords);

        return implode('|', array_map(static fn(array $c) => $c[0] . ',' . $c[1], $coords));
    }
}
