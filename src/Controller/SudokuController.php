<?php

declare(strict_types=1);

namespace Sudoku\Controller;

use Psr\Log\LoggerInterface;
use Sudoku\Base\Exception\InvalidCellValueException;
use Sudoku\Base\Exception\InvalidSudokuException;
use Sudoku\Base\ValueObject\Sudoku;
use Sudoku\Solving\BruteForceSudokuSolver;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class SudokuController
{
    public function __construct(
        private readonly BruteForceSudokuSolver $solver,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('/solve', methods: ['POST', 'OPTIONS'])]
    public function solve(Request $request): JsonResponse
    {
        if ($request->getMethod() === 'OPTIONS') {
            return $this->corsResponse(new JsonResponse(null, Response::HTTP_NO_CONTENT));
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !isset($data['grid'])) {
            $this->logger->warning('Missing grid in request body');

            return $this->corsResponse(
                new JsonResponse(['error' => 'Missing "grid" field.'], Response::HTTP_BAD_REQUEST)
            );
        }

        $rawGrid = $data['grid'];

        if (!is_array($rawGrid)) {
            return $this->corsResponse(
                new JsonResponse(['error' => '"grid" must be an array.'], Response::HTTP_BAD_REQUEST)
            );
        }

        // Normalize: 0 / '' / null → null; everything else → int
        $grid = array_map(
            static fn(mixed $row) => is_array($row)
                ? array_map(
                    static fn(mixed $v) => ($v === 0 || $v === '0' || $v === '' || $v === null)
                        ? null
                        : (int) $v,
                    $row,
                )
                : $row,
            $rawGrid,
        );

        try {
            $sudoku = new Sudoku($grid);
        } catch (InvalidSudokuException|InvalidCellValueException $e) {
            $this->logger->warning('Invalid sudoku input', ['message' => $e->getMessage()]);

            return $this->corsResponse(
                new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        try {
            $result = $this->solver->solve($sudoku);
        } catch (\Throwable $e) {
            $this->logger->error('Solver exception', ['message' => $e->getMessage()]);

            return $this->corsResponse(
                new JsonResponse(['error' => 'Failed to solve the puzzle.'], Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }

        if (!$result->getSudoku()->isSolved()) {
            $this->logger->info('Sudoku has no solution');

            return $this->corsResponse(
                new JsonResponse(['error' => 'This sudoku has no solution.'], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        $this->logger->info('Sudoku solved successfully', [
            'steps' => count($result->getSteps()),
        ]);

        return $this->corsResponse(
            new JsonResponse(['grid' => $result->getSudoku()->toGrid()])
        );
    }

    private function corsResponse(JsonResponse $response): JsonResponse
    {
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');

        return $response;
    }
}
