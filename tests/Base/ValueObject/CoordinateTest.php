<?php

declare(strict_types=1);

namespace Sudoku\Tests\Base\ValueObject;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sudoku\Base\Exception\InvalidCoordinateException;
use Sudoku\Base\ValueObject\Coordinate;

/**
 * docker compose exec php vendor/bin/phpunit tests/Base/ValueObject/CoordinateTest.php
 */
final class CoordinateTest extends TestCase
{
    public function testConstructorStoresRowAndCol(): void
    {
        $coordinate = new Coordinate(3, 7);

        self::assertSame(3, $coordinate->row);
        self::assertSame(7, $coordinate->col);
    }

    #[DataProvider('validCoordinatesProvider')]
    public function testConstructorAcceptsValidCoordinates(int $row, int $col): void
    {
        $coordinate = new Coordinate($row, $col);

        self::assertSame($row, $coordinate->row);
        self::assertSame($col, $coordinate->col);
    }

    /**
     * @return array<string, array{int, int}>
     */
    public static function validCoordinatesProvider(): array
    {
        return [
            'top-left corner' => [0, 0],
            'top-right corner' => [0, 8],
            'bottom-left corner' => [8, 0],
            'bottom-right corner' => [8, 8],
            'center' => [4, 4],
        ];
    }

    #[DataProvider('invalidRowProvider')]
    public function testConstructorThrowsOnInvalidRow(int $row): void
    {
        $this->expectException(InvalidCoordinateException::class);
        $this->expectExceptionMessage(sprintf('Row must be between 0 and 8, %d given.', $row));

        new Coordinate($row, 0);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function invalidRowProvider(): array
    {
        return [
            'negative' => [-1],
            'too large' => [9],
            'way too large' => [100],
        ];
    }

    #[DataProvider('invalidColProvider')]
    public function testConstructorThrowsOnInvalidCol(int $col): void
    {
        $this->expectException(InvalidCoordinateException::class);
        $this->expectExceptionMessage(sprintf('Col must be between 0 and 8, %d given.', $col));

        new Coordinate(0, $col);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function invalidColProvider(): array
    {
        return [
            'negative' => [-1],
            'too large' => [9],
            'way too large' => [100],
        ];
    }
}
