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
    case HiddenPair = 'hidden_pair';
    case NakedTriple = 'naked_triple';
    case NakedQuad = 'naked_quad';
    case HiddenTriple = 'hidden_triple';
    case HiddenQuad = 'hidden_quad';
    case XWing = 'x_wing';
    case XyWing = 'xy_wing';
    case XyzWing = 'xyz_wing';
    case Swordfish = 'swordfish';
}
