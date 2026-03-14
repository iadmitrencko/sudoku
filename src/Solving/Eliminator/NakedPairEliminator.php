<?php

declare(strict_types=1);

namespace Sudoku\Solving\Eliminator;

use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\EliminatorInterface;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ValueObject\EliminationEntry;

final class NakedPairEliminator implements EliminatorInterface
{
    public function getPriority(): int
    {
        return 70;
    }

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
        $pairs = [];

        foreach ($group as $coordinate) {
            $cell = $sudoku->getRow($coordinate->getRow())[$coordinate->getCol()];
            if (!$cell->isEmpty()) {
                continue;
            }

            $candidates = $cell->getCandidates();
            if (count($candidates) !== 2) {
                continue;
            }

            $key = implode(',', $candidates);
            $pairs[$key][] = $coordinate;
        }

        $entries = [];

        foreach ($pairs as $key => $coords) {
            if (count($coords) !== 2) {
                continue;
            }

            $pairValues = array_map('intval', explode(',', $key));

            foreach ($group as $coordinate) {
                $cell = $sudoku->getRow($coordinate->getRow())[$coordinate->getCol()];
                if (!$cell->isEmpty()) {
                    continue;
                }

                if ($this->isSameCoordinates($coordinate, $coords[0]) || $this->isSameCoordinates($coordinate, $coords[1])) {
                    continue;
                }

                foreach ($pairValues as $value) {
                    if (in_array($value, $cell->getCandidates(), true)) {
                        $cell->removeCandidate($value);
                        $entries[] = new EliminationEntry($coordinate, $value, Technique::NakedPair);
                    }
                }
            }
        }

        return $entries;
    }

    private function isSameCoordinates(Coordinate $a, Coordinate $b): bool
    {
        return $a->getRow() === $b->getRow() && $a->getCol() === $b->getCol();
    }
}
