<?php

declare(strict_types=1);

namespace Sudoku\Solving\Eliminator;

use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\EliminatorInterface;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ValueObject\EliminationEntry;

final class NakedTripleEliminator implements EliminatorInterface
{
    public function getPriority(): int
    {
        return 50;
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
        $candidates = [];
        foreach ($group as $coord) {
            $cell = $sudoku->getRow($coord->getRow())[$coord->getCol()];
            if ($cell->isEmpty() && count($cell->getCandidates()) <= 3) {
                $candidates[] = $coord;
            }
        }

        $entries = [];
        $n = count($candidates);

        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                for ($k = $j + 1; $k < $n; $k++) {
                    $triple = [$candidates[$i], $candidates[$j], $candidates[$k]];

                    $union = [];
                    foreach ($triple as $coord) {
                        $union = array_unique(array_merge($union, $sudoku->getRow($coord->getRow())[$coord->getCol()]->getCandidates()));
                    }

                    if (count($union) !== 3) {
                        continue;
                    }

                    foreach ($group as $coord) {
                        $cell = $sudoku->getRow($coord->getRow())[$coord->getCol()];
                        if (!$cell->isEmpty() || $this->inTriple($coord, $triple)) {
                            continue;
                        }

                        foreach ($union as $value) {
                            if (in_array($value, $cell->getCandidates(), true)) {
                                $cell->removeCandidate($value);
                                $entries[] = new EliminationEntry($coord, $value, Technique::NakedTriple);
                            }
                        }
                    }
                }
            }
        }

        return $entries;
    }

    /**
     * @param Coordinate[] $triple
     */
    private function inTriple(Coordinate $coord, array $triple): bool
    {
        foreach ($triple as $t) {
            if ($t->getRow() === $coord->getRow() && $t->getCol() === $coord->getCol()) {
                return true;
            }
        }

        return false;
    }
}
