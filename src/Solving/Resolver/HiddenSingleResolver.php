<?php

declare(strict_types=1);

namespace Sudoku\Solving\Resolver;

use Sudoku\Base\Exception\InvalidCellValueException;
use Sudoku\Base\ValueObject\Cell;
use Sudoku\Base\ValueObject\Coordinate;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\Enum\Technique;
use Sudoku\Solving\ResolverInterface;

final class HiddenSingleResolver implements ResolverInterface
{
    public function getTechnique(): Technique
    {
        return Technique::HiddenSingle;
    }

    public function resolve(Sudoku $sudoku): array
    {
        $resolved = [];

        for ($i = 0; $i < 9; $i++) {
            $rowGroup = array_map(
                static fn(int $col) => new Coordinate($i, $col),
                range(0, 8),
            );
            $this->resolveGroup($sudoku, $rowGroup, $resolved);

            $colGroup = array_map(
                static fn(int $row) => new Coordinate($row, $i),
                range(0, 8),
            );
            $this->resolveGroup($sudoku, $colGroup, $resolved);

            $startRow = intdiv($i, 3) * 3;
            $startCol = ($i % 3) * 3;
            $blockGroup = [];
            for ($r = $startRow; $r < $startRow + 3; $r++) {
                for ($c = $startCol; $c < $startCol + 3; $c++) {
                    $blockGroup[] = new Coordinate($r, $c);
                }
            }
            $this->resolveGroup($sudoku, $blockGroup, $resolved);
        }

        return $resolved;
    }

    /**
     * @param Coordinate[] $group
     * @param Coordinate[] $resolved
     *
     * @throws InvalidCellValueException
     */
    private function resolveGroup(Sudoku $sudoku, array $group, array &$resolved): void
    {
        $placedInGroup = array_filter(
            array_map(static fn(Coordinate $coordinate) => $sudoku->getRow($coordinate->getRow())[$coordinate->getCol()]->getValue(), $group),
        );

        for ($num = 1; $num <= 9; $num++) {
            if (in_array($num, $placedInGroup, true)) {
                continue;
            }

            $possibleCoords = [];

            foreach ($group as $coordinate) {
                $cell = $sudoku->getRow($coordinate->getRow())[$coordinate->getCol()];

                if (!$cell->isEmpty()) {
                    continue;
                }

                if (!in_array($num, $this->collectUsed($sudoku, $coordinate), true)) {
                    $possibleCoords[] = $coordinate;
                }
            }

            if (count($possibleCoords) === 1) {
                $coordinate = $possibleCoords[0];
                $sudoku->getRow($coordinate->getRow())[$coordinate->getCol()]->setValue($num);
                $resolved[] = $coordinate;
            }
        }
    }

    /**
     * @return int[]
     */
    private function collectUsed(Sudoku $sudoku, Coordinate $coordinate): array
    {
        $row = $coordinate->getRow();
        $col = $coordinate->getCol();
        $block = intdiv($row, 3) * 3 + intdiv($col, 3);

        $values = array_merge(
            array_map(static fn(Cell $c) => $c->getValue(), $sudoku->getRow($row)),
            array_map(static fn(Cell $c) => $c->getValue(), $sudoku->getCol($col)),
            array_map(static fn(Cell $c) => $c->getValue(), $sudoku->getBlock($block)),
        );

        return array_values(array_unique(array_filter($values)));
    }
}
