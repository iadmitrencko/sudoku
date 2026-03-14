<?php

declare(strict_types=1);

namespace Sudoku\Tests\Base\ValueObject;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sudoku\Base\Exception\InvalidSudokuException;
use Sudoku\Base\ValueObject\Cell;
use Sudoku\Base\ValueObject\Sudoku;

/**
 * docker compose exec php vendor/bin/phpunit tests/Base/ValueObject/SudokuTest.php
 */
final class SudokuTest extends TestCase
{
    private const array SOLVED_GRID = [
        [5, 3, 4, 6, 7, 8, 9, 1, 2],
        [6, 7, 2, 1, 9, 5, 3, 4, 8],
        [1, 9, 8, 3, 4, 2, 5, 6, 7],
        [8, 5, 9, 7, 6, 1, 4, 2, 3],
        [4, 2, 6, 8, 5, 3, 7, 9, 1],
        [7, 1, 3, 9, 2, 4, 8, 5, 6],
        [9, 6, 1, 5, 3, 7, 2, 8, 4],
        [2, 8, 7, 4, 1, 9, 6, 3, 5],
        [3, 4, 5, 2, 8, 6, 1, 7, 9],
    ];

    private const array PARTIAL_GRID = [
        [5, 3, null, null, 7, null, null, null, null],
        [6, null, null, 1, 9, 5, null, null, null],
        [null, 9, 8, null, null, null, null, 6, null],
        [8, null, null, null, 6, null, null, null, 3],
        [4, null, null, 8, null, 3, null, null, 1],
        [7, null, null, null, 2, null, null, null, 6],
        [null, 6, null, null, null, null, 2, 8, null],
        [null, null, null, 4, 1, 9, null, null, 5],
        [null, null, null, null, 8, null, null, 7, 9],
    ];

    // -------------------------------------------------------------------------
    // __construct / validation
    // -------------------------------------------------------------------------

    public function testConstructorAcceptsValidSolvedGrid(): void
    {
        $sudoku = new Sudoku(self::SOLVED_GRID);

        self::assertInstanceOf(Sudoku::class, $sudoku);
    }

    public function testConstructorAcceptsPartialGrid(): void
    {
        $sudoku = new Sudoku(self::PARTIAL_GRID);

        self::assertInstanceOf(Sudoku::class, $sudoku);
    }

    public function testConstructorAcceptsAllNullGrid(): void
    {
        $empty = array_fill(0, 9, array_fill(0, 9, null));

        $sudoku = new Sudoku($empty);

        self::assertInstanceOf(Sudoku::class, $sudoku);
    }

    public function testConstructorThrowsWhenRowCountIsNot9(): void
    {
        $grid = array_fill(0, 8, array_fill(0, 9, null));

        $this->expectException(InvalidSudokuException::class);
        $this->expectExceptionMessage('Grid must have exactly 9 rows.');

        new Sudoku($grid);
    }

    public function testConstructorThrowsWhenColCountIsNot9(): void
    {
        $grid = array_fill(0, 9, array_fill(0, 9, null));
        $grid[0] = array_fill(0, 8, null);

        $this->expectException(InvalidSudokuException::class);
        $this->expectExceptionMessage('Row 0 must have exactly 9 cells.');

        new Sudoku($grid);
    }

    public function testConstructorThrowsWhenCellValueIsZero(): void
    {
        $grid = array_fill(0, 9, array_fill(0, 9, null));
        $grid[0][0] = 0;

        $this->expectException(InvalidSudokuException::class);

        new Sudoku($grid);
    }

    public function testConstructorThrowsWhenCellValueIsGreaterThan9(): void
    {
        $grid = array_fill(0, 9, array_fill(0, 9, null));
        $grid[0][0] = 10;

        $this->expectException(InvalidSudokuException::class);

        new Sudoku($grid);
    }

    public function testConstructorThrowsOnDuplicateInRow(): void
    {
        $grid = array_fill(0, 9, array_fill(0, 9, null));
        $grid[0][0] = 5;
        $grid[0][1] = 5;

        $this->expectException(InvalidSudokuException::class);
        $this->expectExceptionMessage('Duplicate values found in row 0.');

        new Sudoku($grid);
    }

    public function testConstructorThrowsOnDuplicateInColumn(): void
    {
        $grid = array_fill(0, 9, array_fill(0, 9, null));
        $grid[0][0] = 5;
        $grid[1][0] = 5;

        $this->expectException(InvalidSudokuException::class);
        $this->expectExceptionMessage('Duplicate values found in column 0.');

        new Sudoku($grid);
    }

    public function testConstructorThrowsOnDuplicateInBlock(): void
    {
        $grid = array_fill(0, 9, array_fill(0, 9, null));
        $grid[0][0] = 5;
        $grid[1][1] = 5;

        $this->expectException(InvalidSudokuException::class);
        $this->expectExceptionMessage('Duplicate values found in block 0.');

        new Sudoku($grid);
    }

    // -------------------------------------------------------------------------
    // getRow
    // -------------------------------------------------------------------------

    /**
     * @param int[] $expected
     */
    #[DataProvider('rowProvider')]
    public function testGetRowReturnsCorrectCells(int $rowIndex, array $expected): void
    {
        $sudoku = new Sudoku(self::SOLVED_GRID);
        $row = $sudoku->getRow($rowIndex);

        self::assertCount(9, $row);
        self::assertContainsOnlyInstancesOf(Cell::class, $row);

        $values = array_map(static fn(Cell $c) => $c->getValue(), $row);
        self::assertSame($expected, $values);
    }

    /**
     * @return array<string, array{int, int[]}>
     */
    public static function rowProvider(): array
    {
        return [
            'first row' => [0, [5, 3, 4, 6, 7, 8, 9, 1, 2]],
            'middle row' => [4, [4, 2, 6, 8, 5, 3, 7, 9, 1]],
            'last row'  => [8, [3, 4, 5, 2, 8, 6, 1, 7, 9]],
        ];
    }

    // -------------------------------------------------------------------------
    // getCol
    // -------------------------------------------------------------------------

    /**
     * @param int[] $expected
     */
    #[DataProvider('colProvider')]
    public function testGetColReturnsCorrectCells(int $colIndex, array $expected): void
    {
        $sudoku = new Sudoku(self::SOLVED_GRID);
        $col = $sudoku->getCol($colIndex);

        self::assertCount(9, $col);
        self::assertContainsOnlyInstancesOf(Cell::class, $col);

        $values = array_map(static fn(Cell $c) => $c->getValue(), $col);
        self::assertSame($expected, $values);
    }

    /**
     * @return array<string, array{int, int[]}>
     */
    public static function colProvider(): array
    {
        return [
            'first column'  => [0, [5, 6, 1, 8, 4, 7, 9, 2, 3]],
            'middle column' => [4, [7, 9, 4, 6, 5, 2, 3, 1, 8]],
            'last column'   => [8, [2, 8, 7, 3, 1, 6, 4, 5, 9]],
        ];
    }

    // -------------------------------------------------------------------------
    // getBlock
    // -------------------------------------------------------------------------

    /**
     * @param int[] $expected
     */
    #[DataProvider('blockProvider')]
    public function testGetBlockReturnsCorrectCells(int $blockIndex, array $expected): void
    {
        $sudoku = new Sudoku(self::SOLVED_GRID);
        $block = $sudoku->getBlock($blockIndex);

        self::assertCount(9, $block);
        self::assertContainsOnlyInstancesOf(Cell::class, $block);

        $values = array_map(static fn(Cell $c) => $c->getValue(), $block);
        self::assertSame($expected, $values);
    }

    /**
     * @return array<string, array{int, int[]}>
     */
    public static function blockProvider(): array
    {
        return [
            'top-left block (0)'     => [0, [5, 3, 4, 6, 7, 2, 1, 9, 8]],
            'top-center block (1)'   => [1, [6, 7, 8, 1, 9, 5, 3, 4, 2]],
            'top-right block (2)'    => [2, [9, 1, 2, 3, 4, 8, 5, 6, 7]],
            'middle-left block (3)'  => [3, [8, 5, 9, 4, 2, 6, 7, 1, 3]],
            'center block (4)'       => [4, [7, 6, 1, 8, 5, 3, 9, 2, 4]],
            'middle-right block (5)' => [5, [4, 2, 3, 7, 9, 1, 8, 5, 6]],
            'bottom-left block (6)'  => [6, [9, 6, 1, 2, 8, 7, 3, 4, 5]],
            'bottom-center block (7)'=> [7, [5, 3, 7, 4, 1, 9, 2, 8, 6]],
            'bottom-right block (8)' => [8, [2, 8, 4, 6, 3, 5, 1, 7, 9]],
        ];
    }

    // -------------------------------------------------------------------------
    // isSolved
    // -------------------------------------------------------------------------

    public function testIsSolvedReturnsTrueForCompletedGrid(): void
    {
        $sudoku = new Sudoku(self::SOLVED_GRID);

        self::assertTrue($sudoku->isSolved());
    }

    public function testIsSolvedReturnsFalseForPartialGrid(): void
    {
        $sudoku = new Sudoku(self::PARTIAL_GRID);

        self::assertFalse($sudoku->isSolved());
    }

    public function testIsSolvedReturnsFalseForEmptyGrid(): void
    {
        $sudoku = new Sudoku(array_fill(0, 9, array_fill(0, 9, null)));

        self::assertFalse($sudoku->isSolved());
    }

    // -------------------------------------------------------------------------
    // toGrid
    // -------------------------------------------------------------------------

    public function testToGridReturnsSameValuesAsInput(): void
    {
        $sudoku = new Sudoku(self::SOLVED_GRID);

        self::assertSame(self::SOLVED_GRID, $sudoku->toGrid());
    }

    public function testToGridPreservesNullsForEmptyCells(): void
    {
        $sudoku = new Sudoku(self::PARTIAL_GRID);

        self::assertSame(self::PARTIAL_GRID, $sudoku->toGrid());
    }

    public function testToGridReturnsNineByNineMatrix(): void
    {
        $sudoku = new Sudoku(self::SOLVED_GRID);
        $grid = $sudoku->toGrid();

        self::assertCount(9, $grid);
        foreach ($grid as $row) {
            self::assertCount(9, $row);
        }
    }

    public function testToGridIsIndependentOfInternalState(): void
    {
        $sudoku = new Sudoku(self::SOLVED_GRID);

        self::assertSame($sudoku->toGrid(), $sudoku->toGrid());
    }
}
