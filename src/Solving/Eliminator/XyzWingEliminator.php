<?php

declare(strict_types=1);

namespace Sudoku\Solving\Eliminator;

use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\EliminatorInterface;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ValueObject\EliminationEntry;

final class XyzWingEliminator implements EliminatorInterface
{
    public function getPriority(): int
    {
        return 18;
    }

    /**
     * @return EliminationEntry[]
     */
    public function eliminate(Sudoku $sudoku): array
    {
        $trivalue = $this->collectTrivalue($sudoku);
        $bivalue = $this->collectBivalue($sudoku);
        $entries = [];

        foreach ($trivalue as [$pivotCoord, $pivotCandidates]) {
            $bivaluePeers = array_filter(
                $bivalue,
                fn(array $item) => $this->sees($pivotCoord, $item[0])
                    && array_diff($item[1], $pivotCandidates) === [],
            );
            $bivaluePeers = array_values($bivaluePeers);

            $n = count($bivaluePeers);
            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    [$p1Coord, $p1Candidates] = $bivaluePeers[$i];
                    [$p2Coord, $p2Candidates] = $bivaluePeers[$j];

                    // Union of both pincers must equal all 3 pivot candidates
                    $union = array_unique(array_merge($p1Candidates, $p2Candidates));
                    sort($union);
                    $sorted = $pivotCandidates;
                    sort($sorted);
                    if ($union !== $sorted) {
                        continue;
                    }

                    // X is the shared candidate between the two pincers
                    $x = array_values(array_intersect($p1Candidates, $p2Candidates));
                    if (count($x) !== 1) {
                        continue;
                    }
                    $x = $x[0];

                    // Eliminate X from cells that see pivot, p1, and p2
                    for ($r = 0; $r < 9; $r++) {
                        for ($c = 0; $c < 9; $c++) {
                            $dCoord = new Coordinate($r, $c);
                            if ($this->sameCoord($dCoord, $pivotCoord)
                                || $this->sameCoord($dCoord, $p1Coord)
                                || $this->sameCoord($dCoord, $p2Coord)) {
                                continue;
                            }

                            $cell = $sudoku->getRow($r)[$c];
                            if (!$cell->isEmpty()) {
                                continue;
                            }

                            if ($this->sees($dCoord, $pivotCoord)
                                && $this->sees($dCoord, $p1Coord)
                                && $this->sees($dCoord, $p2Coord)) {
                                if (in_array($x, $cell->getCandidates(), true)) {
                                    $cell->removeCandidate($x);
                                    $entries[] = new EliminationEntry($dCoord, $x, Technique::XyzWing);
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
    private function collectTrivalue(Sudoku $sudoku): array
    {
        $result = [];
        for ($r = 0; $r < 9; $r++) {
            for ($c = 0; $c < 9; $c++) {
                $cell = $sudoku->getRow($r)[$c];
                if ($cell->isEmpty() && count($cell->getCandidates()) === 3) {
                    $result[] = [new Coordinate($r, $c), array_values($cell->getCandidates())];
                }
            }
        }

        return $result;
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
