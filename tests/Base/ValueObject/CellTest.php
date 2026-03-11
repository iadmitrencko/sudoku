<?php

declare(strict_types=1);

namespace Sudoku\Tests\Base\ValueObject;

use PHPUnit\Framework\TestCase;
use Sudoku\Base\Exception\InvalidCellValueException;
use Sudoku\Base\ValueObject\Cell;
use Throwable;

/**
 * docker compose exec php vendor/bin/phpunit tests/Base/ValueObject/CellTest.php
 */
final class CellTest extends TestCase
{
    public function testDefaultConstructorCreatesEmptyCell(): void
    {
        $cell = new Cell();

        self::assertNull($cell->getValue());
        self::assertTrue($cell->isEmpty());
    }

    public function testConstructorWithNullCreatesEmptyCell(): void
    {
        $cell = new Cell(null);

        self::assertNull($cell->getValue());
        self::assertTrue($cell->isEmpty());
    }

    /**
     * @throws Throwable
     */
    public function testConstructorWithValidValueStoresIt(): void
    {
        $cell = new Cell(5);

        self::assertSame(5, $cell->getValue());
        self::assertFalse($cell->isEmpty());
    }

    /**
     * @throws Throwable
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('validValues')]
    public function testConstructorAcceptsAllValidValues(int $value): void
    {
        $cell = new Cell($value);

        self::assertSame($value, $cell->getValue());
    }

    /**
     * @return array<int, array{int}>
     */
    public static function validValues(): array
    {
        return array_map(static fn(int $v) => [$v], range(1, 9));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidValues')]
    public function testConstructorRejectsInvalidValue(int $value): void
    {
        $this->expectException(InvalidCellValueException::class);
        $this->expectExceptionMessage(sprintf('Cell value must be between 1 and 9, %d given.', $value));

        new Cell($value);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function invalidValues(): array
    {
        return [
            'zero' => [0],
            'negative' => [-1],
            'ten' => [10],
            'large' => [100],
        ];
    }

    public function testEmptyCellHasAllCandidates(): void
    {
        $cell = new Cell();

        self::assertSame(range(1, 9), $cell->getCandidates());
    }

    public function testFilledCellHasNoCandidates(): void
    {
        $cell = new Cell(3);

        self::assertSame([], $cell->getCandidates());
    }

    /**
     * @throws Throwable
     */
    public function testSetValueStoresValue(): void
    {
        $cell = new Cell();
        $cell->setValue(7);

        self::assertSame(7, $cell->getValue());
        self::assertFalse($cell->isEmpty());
    }

    /**
     * @throws Throwable
     */
    public function testSetValueClearsCandidates(): void
    {
        $cell = new Cell();
        $cell->setValue(7);

        self::assertSame([], $cell->getCandidates());
    }

    public function testSetValueRejectsInvalidValue(): void
    {
        $cell = new Cell();

        $this->expectException(InvalidCellValueException::class);
        $this->expectExceptionMessage('Cell value must be between 1 and 9, 10 given.');

        $cell->setValue(10);
    }

    public function testRemoveCandidateRemovesIt(): void
    {
        $cell = new Cell();
        $cell->removeCandidate(5);

        self::assertNotContains(5, $cell->getCandidates());
        self::assertCount(8, $cell->getCandidates());
    }

    public function testRemoveCandidateOnNonExistentValueDoesNothing(): void
    {
        $cell = new Cell();
        $cell->removeCandidate(5);
        $cell->removeCandidate(5);

        self::assertCount(8, $cell->getCandidates());
    }
}
