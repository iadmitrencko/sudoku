<?php

declare(strict_types=1);

namespace Sudoku\Solving;

use Sudoku\Base\ValueObject\Cell;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\ValueObject\ResolvedCell;
use Sudoku\Solving\ValueObject\SolvingResult;

final class SudokuSolver
{
    /**
     * @param iterable<ResolverInterface> $resolvers
     * @param iterable<EliminatorInterface> $eliminators
     */
    public function __construct(
        private readonly iterable $resolvers,
        private readonly iterable $eliminators,
    ) {
    }

    public function solve(Sudoku $sudoku): SolvingResult
    {
        $steps = [];
        $resolvers = iterator_to_array($this->resolvers);
        usort($resolvers, static fn(ResolverInterface $a, ResolverInterface $b) => $b->getPriority() <=> $a->getPriority());

        $eliminators = iterator_to_array($this->eliminators);
        usort($eliminators, static fn(EliminatorInterface $a, EliminatorInterface $b) => $b->getPriority() <=> $a->getPriority());

        $this->initCandidates($sudoku);

        do {
            $progress = false;

            foreach ($resolvers as $resolver) {
                foreach ($resolver->resolve($sudoku) as $coordinate) {
                    $value = $sudoku->getRow($coordinate->getRow())[$coordinate->getCol()]->getValue();
                    $this->propagateCandidates($sudoku, $coordinate->getRow(), $coordinate->getCol(), $value);
                    $steps[] = new ResolvedCell($coordinate, $resolver->getTechnique(), $value);
                    $progress = true;
                }
            }

            if (!$progress) {
                foreach ($eliminators as $eliminator) {
                    $entries = $eliminator->eliminate($sudoku);
                    if ($entries !== []) {
                        array_push($steps, ...$entries);
                        $progress = true;
                    }
                }
            }
        } while ($progress && !$sudoku->isSolved());

        return new SolvingResult($sudoku, $steps);
    }

    private function initCandidates(Sudoku $sudoku): void
    {
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                $cell = $sudoku->getRow($row)[$col];

                if (!$cell->isEmpty()) {
                    continue;
                }

                $block = intdiv($row, 3) * 3 + intdiv($col, 3);
                $used = array_unique(array_filter(array_merge(
                    array_map(static fn(Cell $c) => $c->getValue(), $sudoku->getRow($row)),
                    array_map(static fn(Cell $c) => $c->getValue(), $sudoku->getCol($col)),
                    array_map(static fn(Cell $c) => $c->getValue(), $sudoku->getBlock($block)),
                )));

                foreach ($used as $value) {
                    $cell->removeCandidate($value);
                }
            }
        }
    }

    private function propagateCandidates(Sudoku $sudoku, int $row, int $col, int $value): void
    {
        $block = intdiv($row, 3) * 3 + intdiv($col, 3);

        foreach ($sudoku->getRow($row) as $cell) {
            $cell->removeCandidate($value);
        }
        foreach ($sudoku->getCol($col) as $cell) {
            $cell->removeCandidate($value);
        }
        foreach ($sudoku->getBlock($block) as $cell) {
            $cell->removeCandidate($value);
        }
    }
}
