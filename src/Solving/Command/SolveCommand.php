<?php

declare(strict_types=1);

namespace Sudoku\Solving\Command;

use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\SudokuSolver;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(name: 'app:solve', description: 'Вирішити судоку')]
final class SolveCommand extends Command
{
    public function __construct(
        private readonly SudokuSolver $solver,
    ) {
        parent::__construct();
    }

    /**
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Заповни блоки: null — порожня ячейка, 1-9 — задане значення
        $b0 = [
            [null, null, null],
            [null, null, null],
            [null, null, null],
        ];

        $b1 = [
            [null, null, null],
            [null, null, null],
            [null, null, null],
        ];

        $b2 = [
            [null, null, null],
            [null, null, null],
            [null, null, null],
        ];

        $b3 = [
            [null, null, null],
            [null, null, null],
            [null, null, null],
        ];

        $b4 = [
            [null, null, null],
            [null, null, null],
            [null, null, null],
        ];

        $b5 = [
            [null, null, null],
            [null, null, null],
            [null, null, null],
        ];

        $b6 = [
            [null, null, null],
            [null, null, null],
            [null, null, null],
        ];

        $b7 = [
            [null, null, null],
            [null, null, null],
            [null, null, null],
        ];

        $b8 = [
            [null, null, null],
            [null, null, null],
            [null, null, null],
        ];

        $grid = [
            [...$b0[0], ...$b1[0], ...$b2[0]],
            [...$b0[1], ...$b1[1], ...$b2[1]],
            [...$b0[2], ...$b1[2], ...$b2[2]],
            [...$b3[0], ...$b4[0], ...$b5[0]],
            [...$b3[1], ...$b4[1], ...$b5[1]],
            [...$b3[2], ...$b4[2], ...$b5[2]],
            [...$b6[0], ...$b7[0], ...$b8[0]],
            [...$b6[1], ...$b7[1], ...$b8[1]],
            [...$b6[2], ...$b7[2], ...$b8[2]],
        ];

        $sudoku = new Sudoku($grid);
        $result = $this->solver->solve($sudoku);

        $output->writeln(sprintf('Вирішено ячейок: %d', count($result->getLog())));
        $output->writeln($result->getSudoku()->isSolved() ? 'Судоку вирішено!' : 'Судоку не вирішено повністю.');
        $output->writeln('');

        $separator = '------+-------+------';
        foreach ($result->getSudoku()->toGrid() as $rowIndex => $row) {
            if ($rowIndex > 0 && $rowIndex % 3 === 0) {
                $output->writeln($separator);
            }
            $cells = array_map(static fn(?int $v) => $v ?? '.', $row);
            $output->writeln(sprintf(
                '%s %s %s | %s %s %s | %s %s %s',
                ...$cells,
            ));
        }

        $output->writeln('');
        $output->writeln('Послідовність:');

        foreach ($result->getLog() as $i => $resolved) {
            $coordinate = $resolved->getCoordinate();
            $output->writeln(sprintf(
                '%d. [%d,%d] — %s',
                $i + 1,
                $coordinate->getRow(),
                $coordinate->getCol(),
                $resolved->getTechnique()->value,
            ));
        }

        return Command::SUCCESS;
    }
}
