<?php

declare(strict_types=1);

namespace Sudoku\Solving\Eliminator;

use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\EliminatorInterface;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ValueObject\EliminationEntry;

final class SimpleColoringEliminator implements EliminatorInterface
{
    public function getPriority(): int
    {
        return 15;
    }

    /**
     * @return EliminationEntry[]
     */
    public function eliminate(Sudoku $sudoku): array
    {
        $entries = [];

        for ($digit = 1; $digit <= 9; $digit++) {
            $graph = $this->buildStrongLinkGraph($sudoku, $digit);
            $components = $this->colorComponents($graph);

            foreach ($components as $coloring) {
                $byColor = [0 => [], 1 => []];
                foreach ($coloring as $key => $color) {
                    $byColor[$color][] = $this->keyToCoord($key);
                }

                // Rule 2 — Color Wrap: two cells of same color see each other → that color is impossible
                $eliminatedColor = $this->findWrappedColor($byColor);
                if ($eliminatedColor !== null) {
                    foreach ($byColor[$eliminatedColor] as $coord) {
                        $cell = $sudoku->getRow($coord->getRow())[$coord->getCol()];
                        if ($cell->isEmpty() && in_array($digit, $cell->getCandidates(), true)) {
                            $cell->removeCandidate($digit);
                            $entries[] = new EliminationEntry($coord, $digit, Technique::SimpleColoring);
                        }
                    }
                    continue;
                }

                // Rule 1 — Color Trap: cell outside chain sees both colors → eliminate
                for ($r = 0; $r < 9; $r++) {
                    for ($c = 0; $c < 9; $c++) {
                        $coord = new Coordinate($r, $c);
                        if (isset($coloring[$this->coordToKey($coord)])) {
                            continue;
                        }

                        $cell = $sudoku->getRow($r)[$c];
                        if (!$cell->isEmpty() || !in_array($digit, $cell->getCandidates(), true)) {
                            continue;
                        }

                        if ($this->seesAny($coord, $byColor[0]) && $this->seesAny($coord, $byColor[1])) {
                            $cell->removeCandidate($digit);
                            $entries[] = new EliminationEntry($coord, $digit, Technique::SimpleColoring);
                        }
                    }
                }
            }
        }

        return $entries;
    }

    /**
     * @param array<int, Coordinate[]> $byColor
     */
    private function findWrappedColor(array $byColor): ?int
    {
        foreach ([0, 1] as $color) {
            $coords = $byColor[$color];
            $n = count($coords);
            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    if ($this->sees($coords[$i], $coords[$j])) {
                        return $color;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param Coordinate[] $targets
     */
    private function seesAny(Coordinate $coord, array $targets): bool
    {
        foreach ($targets as $target) {
            if ($this->sees($coord, $target)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, list<string>>
     */
    private function buildStrongLinkGraph(Sudoku $sudoku, int $digit): array
    {
        $graph = [];

        // Rows
        for ($r = 0; $r < 9; $r++) {
            $cells = $this->cellsWithDigitInRow($sudoku, $r, $digit);
            if (count($cells) === 2) {
                $this->addEdge($graph, $cells[0], $cells[1]);
            }
        }

        // Columns
        for ($c = 0; $c < 9; $c++) {
            $cells = $this->cellsWithDigitInCol($sudoku, $c, $digit);
            if (count($cells) === 2) {
                $this->addEdge($graph, $cells[0], $cells[1]);
            }
        }

        // Blocks
        for ($b = 0; $b < 9; $b++) {
            $cells = $this->cellsWithDigitInBlock($sudoku, $b, $digit);
            if (count($cells) === 2) {
                $this->addEdge($graph, $cells[0], $cells[1]);
            }
        }

        return $graph;
    }

    /**
     * @param array<string, list<string>> $graph
     */
    private function addEdge(array &$graph, Coordinate $a, Coordinate $b): void
    {
        $keyA = $this->coordToKey($a);
        $keyB = $this->coordToKey($b);

        if (!isset($graph[$keyA])) {
            $graph[$keyA] = [];
        }
        if (!isset($graph[$keyB])) {
            $graph[$keyB] = [];
        }

        if (!in_array($keyB, $graph[$keyA], true)) {
            $graph[$keyA][] = $keyB;
        }
        if (!in_array($keyA, $graph[$keyB], true)) {
            $graph[$keyB][] = $keyA;
        }
    }

    /**
     * Returns array of components, each component maps 'r,c' → color (0 or 1).
     *
     * @param array<string, list<string>> $graph
     * @return array<array<string, int>>
     */
    private function colorComponents(array $graph): array
    {
        $visited = [];
        $components = [];

        foreach (array_keys($graph) as $startKey) {
            if (isset($visited[$startKey])) {
                continue;
            }

            $coloring = [$startKey => 0];
            $visited[$startKey] = true;
            $queue = [$startKey];

            while (!empty($queue)) {
                $key = array_shift($queue);
                $nextColor = 1 - $coloring[$key];

                foreach ($graph[$key] as $neighborKey) {
                    if (!isset($coloring[$neighborKey])) {
                        $coloring[$neighborKey] = $nextColor;
                        $visited[$neighborKey] = true;
                        $queue[] = $neighborKey;
                    }
                }
            }

            $components[] = $coloring;
        }

        return $components;
    }

    /**
     * @return Coordinate[]
     */
    private function cellsWithDigitInRow(Sudoku $sudoku, int $row, int $digit): array
    {
        $cells = [];
        for ($c = 0; $c < 9; $c++) {
            $cell = $sudoku->getRow($row)[$c];
            if ($cell->isEmpty() && in_array($digit, $cell->getCandidates(), true)) {
                $cells[] = new Coordinate($row, $c);
            }
        }

        return $cells;
    }

    /**
     * @return Coordinate[]
     */
    private function cellsWithDigitInCol(Sudoku $sudoku, int $col, int $digit): array
    {
        $cells = [];
        for ($r = 0; $r < 9; $r++) {
            $cell = $sudoku->getRow($r)[$col];
            if ($cell->isEmpty() && in_array($digit, $cell->getCandidates(), true)) {
                $cells[] = new Coordinate($r, $col);
            }
        }

        return $cells;
    }

    /**
     * @return Coordinate[]
     */
    private function cellsWithDigitInBlock(Sudoku $sudoku, int $block, int $digit): array
    {
        $startRow = intdiv($block, 3) * 3;
        $startCol = ($block % 3) * 3;
        $cells = [];

        for ($r = $startRow; $r < $startRow + 3; $r++) {
            for ($c = $startCol; $c < $startCol + 3; $c++) {
                $cell = $sudoku->getRow($r)[$c];
                if ($cell->isEmpty() && in_array($digit, $cell->getCandidates(), true)) {
                    $cells[] = new Coordinate($r, $c);
                }
            }
        }

        return $cells;
    }

    private function coordToKey(Coordinate $coord): string
    {
        return $coord->getRow() . ',' . $coord->getCol();
    }

    private function keyToCoord(string $key): Coordinate
    {
        [$r, $c] = explode(',', $key);

        return new Coordinate((int) $r, (int) $c);
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
