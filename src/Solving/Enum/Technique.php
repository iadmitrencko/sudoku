<?php

declare(strict_types=1);

namespace Sudoku\Solving\Enum;

enum Technique: string
{
    case RowSingleCandidate = 'row_single_candidate';
    case ColSingleCandidate = 'col_single_candidate';
    case BlockSingleCandidate = 'block_single_candidate';
    case NakedSingle = 'naked_single';
    case HiddenSingle = 'hidden_single';
    case LockedCandidatesPointing = 'locked_candidates_pointing';
    case LockedCandidatesClaiming = 'locked_candidates_claiming';
    case NakedPair = 'naked_pair';
}
