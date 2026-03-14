<?php

declare(strict_types=1);

namespace Sudoku\Base\ValueObject;

use Sudoku\Base\Exception\InvalidCellValueException;
use Sudoku\Base\Exception\InvalidSudokuException;

final class Sudoku
{
    /**
     * @var Cell[][]
     */
    private array $grid;

    /**
     * @param array<int, array<int, int|null>> $grid 9x9 matrix, null for empty cells
     *
     * @throws InvalidSudokuException
     * @throws InvalidCellValueException
     */
    public function __construct(
        array $grid,
    ) {
        $this->validate($grid);

        $this->grid = array_map(
            static fn(array $row) => array_map(static fn(?int $value) => new Cell($value), $row),
            $grid,
        );
    }

    /**
     * @return Cell[]
     */
    public function getRow(int $number): array
    {
        return $this->grid[$number];
    }

    /**
     * @return Cell[]
     */
    public function getCol(int $number): array
    {
        return array_column($this->grid, $number);
    }

    /**
     * @return Cell[]
     */
    public function getBlock(int $number): array
    {
        $startRow = intdiv($number, 3) * 3;
        $startCol = ($number % 3) * 3;

        $cells = [];
        for ($row = $startRow; $row < $startRow + 3; $row++) {
            for ($col = $startCol; $col < $startCol + 3; $col++) {
                $cells[] = $this->grid[$row][$col];
            }
        }

        return $cells;
    }

    /**
     * @return array<int, array<int, int|null>>
     */
    public function toGrid(): array
    {
        return array_map(
            static fn(array $row) => array_map(static fn(Cell $cell) => $cell->getValue(), $row),
            $this->grid,
        );
    }

    public function isSolved(): bool
    {
        for ($i = 0; $i < 9; $i++) {
            if (!$this->isCompleteGroup($this->getRow($i))) {
                return false;
            }

            if (!$this->isCompleteGroup($this->getCol($i))) {
                return false;
            }

            if (!$this->isCompleteGroup($this->getBlock($i))) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, array<int, int|null>> $grid
     * @throws InvalidSudokuException
     */
    private function validate(array $grid): void
    {
        if (count($grid) !== 9) {
            throw new InvalidSudokuException('Grid must have exactly 9 rows.');
        }

        foreach ($grid as $rowIndex => $row) {
            if (!is_array($row) || count($row) !== 9) {
                throw new InvalidSudokuException(sprintf('Row %d must have exactly 9 cells.', $rowIndex));
            }

            foreach ($row as $colIndex => $value) {
                if ($value !== null && (!is_int($value) || $value < 1 || $value > 9)) {
                    throw new InvalidSudokuException(
                        sprintf('Cell [%d][%d] must be null or an integer between 1 and 9.', $rowIndex, $colIndex),
                    );
                }
            }
        }

        for ($i = 0; $i < 9; $i++) {
            $this->validateGroup($this->extractRow($grid, $i), sprintf('row %d', $i));
            $this->validateGroup($this->extractCol($grid, $i), sprintf('column %d', $i));
            $this->validateGroup($this->extractBlock($grid, $i), sprintf('block %d', $i));
        }
    }

    /**
     * @param array<int, int|null> $group
     * @throws InvalidSudokuException
     */
    private function validateGroup(array $group, string $label): void
    {
        $values = array_filter($group, static fn($v) => $v !== null);

        if (count($values) !== count(array_unique($values))) {
            throw new InvalidSudokuException(sprintf('Duplicate values found in %s.', $label));
        }
    }

    /**
     * @param array<int, array<int, int|null>> $grid
     *
     * @return array<int, int|null>
     */
    private function extractRow(array $grid, int $number): array
    {
        return $grid[$number];
    }

    /**
     * @param array<int, array<int, int|null>> $grid
     *
     * @return array<int, int|null>
     */
    private function extractCol(array $grid, int $number): array
    {
        return array_column($grid, $number);
    }

    /**
     * @param array<int, array<int, int|null>> $grid
     *
     * @return array<int, int|null>
     */
    private function extractBlock(array $grid, int $number): array
    {
        $startRow = intdiv($number, 3) * 3;
        $startCol = ($number % 3) * 3;

        $cells = [];
        for ($row = $startRow; $row < $startRow + 3; $row++) {
            for ($col = $startCol; $col < $startCol + 3; $col++) {
                $cells[] = $grid[$row][$col];
            }
        }

        return $cells;
    }

    /**
     * @param Cell[] $cells
     */
    private function isCompleteGroup(array $cells): bool
    {
        $values = array_map(static fn(Cell $cell) => $cell->getValue(), $cells);

        if (in_array(null, $values, true)) {
            return false;
        }

        sort($values);

        return $values === range(1, 9);
    }
}
