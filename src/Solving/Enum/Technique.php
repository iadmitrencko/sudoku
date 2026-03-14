<?php

declare(strict_types=1);

namespace Sudoku\Solving\Enum;

enum Technique: string
{
    case RowSingleCandidate = 'row_single_candidate';
    case ColSingleCandidate = 'col_single_candidate';
    case BlockSingleCandidate = 'block_single_candidate';
    case NakedSingle = 'naked_single';
}
