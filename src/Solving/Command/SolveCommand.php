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
        // Заповни сітку: null — порожня ячейка, 1-9 — задане значення
        $grid = [
            [null, null, null, null, null, null, null, null, null],
            [null, null, null, null, null, null, null, null, null],
            [null, null, null, null, null, null, null, null, null],

            [null, null, null, null, null, null, null, null, null],
            [null, null, null, null, null, null, null, null, null],
            [null, null, null, null, null, null, null, null, null],

            [null, null, null, null, null, null, null, null, null],
            [null, null, null, null, null, null, null, null, null],
            [null, null, null, null, null, null, null, null, null],
        ];

        $sudoku = new Sudoku($grid);
        $result = $this->solver->solve($sudoku);

        $output->writeln(sprintf('Вирішено ячейок: %d', count($result->getLog())));
        $output->writeln($result->getSudoku()->isSolved() ? 'Судоку вирішено!' : 'Судоку не вирішено повністю.');
        $output->writeln('');

        foreach ($result->getSudoku()->toGrid() as $row) {
            $output->writeln(implode(' ', array_map(static fn(?int $v) => $v ?? '.', $row)));
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
