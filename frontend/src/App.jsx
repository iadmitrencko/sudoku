import { useState, useCallback } from 'react';

const API_URL = 'http://localhost:8080/api/solve';

const EMPTY_GRID = () => Array.from({ length: 9 }, () => Array(9).fill(''));

function getConflicts(grid) {
  const conflicts = new Set();

  const check = (cells) => {
    const seen = {};
    cells.forEach(([r, c]) => {
      const v = grid[r][c];
      if (!v) return;
      if (seen[v] !== undefined) {
        conflicts.add(`${r},${c}`);
        conflicts.add(`${seen[v][0]},${seen[v][1]}`);
      } else {
        seen[v] = [r, c];
      }
    });
  };

  for (let i = 0; i < 9; i++) {
    check(Array.from({ length: 9 }, (_, j) => [i, j]));
    check(Array.from({ length: 9 }, (_, j) => [j, i]));
  }

  for (let br = 0; br < 3; br++) {
    for (let bc = 0; bc < 3; bc++) {
      const cells = [];
      for (let r = br * 3; r < br * 3 + 3; r++) {
        for (let c = bc * 3; c < bc * 3 + 3; c++) {
          cells.push([r, c]);
        }
      }
      check(cells);
    }
  }

  return conflicts;
}

export default function App() {
  const [grid, setGrid] = useState(EMPTY_GRID);
  const [givenCells, setGivenCells] = useState(new Set());
  const [solvedCells, setSolvedCells] = useState(new Set());
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [selectedCell, setSelectedCell] = useState(null);

  const conflicts = getConflicts(grid);

  const isSelectedGiven = selectedCell !== null && givenCells.has(selectedCell);

  const handleChange = useCallback((row, col, value) => {
    if (solvedCells.has(`${row},${col}`) && givenCells.has(`${row},${col}`)) return;

    const digit = value.replace(/[^1-9]/g, '').slice(-1);
    setGrid((prev) => {
      const next = prev.map((r) => [...r]);
      next[row][col] = digit;
      return next;
    });
    setSolvedCells(new Set());
    setError(null);
  }, [solvedCells, givenCells]);

  const handleKeyDown = useCallback((e, row, col) => {
    if (e.key === 'Backspace' || e.key === 'Delete') {
      if (givenCells.has(`${row},${col}`)) return;
      setGrid((prev) => {
        const next = prev.map((r) => [...r]);
        next[row][col] = '';
        return next;
      });
      setSolvedCells(new Set());
      setError(null);
    }

    const arrowMap = {
      ArrowUp: [-1, 0],
      ArrowDown: [1, 0],
      ArrowLeft: [0, -1],
      ArrowRight: [0, 1],
    };
    if (arrowMap[e.key]) {
      e.preventDefault();
      const [dr, dc] = arrowMap[e.key];
      const nr = Math.max(0, Math.min(8, row + dr));
      const nc = Math.max(0, Math.min(8, col + dc));
      document.getElementById(`cell-${nr}-${nc}`)?.focus();
    }
  }, [givenCells]);

  const handleNumpadDigit = useCallback((digit) => {
    if (!selectedCell || isSelectedGiven) return;
    const [r, c] = selectedCell.split(',').map(Number);
    setGrid((prev) => {
      const next = prev.map((row) => [...row]);
      next[r][c] = String(digit);
      return next;
    });
    setSolvedCells(new Set());
    setError(null);
  }, [selectedCell, isSelectedGiven]);

  const handleClearCell = useCallback(() => {
    if (!selectedCell || isSelectedGiven) return;
    const [r, c] = selectedCell.split(',').map(Number);
    setGrid((prev) => {
      const next = prev.map((row) => [...row]);
      next[r][c] = '';
      return next;
    });
    setSolvedCells(new Set());
    setError(null);
  }, [selectedCell, isSelectedGiven]);

  const handleSolve = async () => {
    if (conflicts.size > 0) {
      setError('Виправте конфлікти перед розв\'язанням.');
      return;
    }

    const numericGrid = grid.map((row) =>
      row.map((v) => (v === '' ? 0 : parseInt(v, 10)))
    );

    const given = new Set();
    grid.forEach((row, r) =>
      row.forEach((v, c) => {
        if (v !== '') given.add(`${r},${c}`);
      })
    );

    setLoading(true);
    setError(null);

    try {
      const res = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ grid: numericGrid }),
      });

      const data = await res.json();

      if (!res.ok) {
        setError(data.error ?? 'Невідома помилка.');
        return;
      }

      const solved = new Set();
      data.grid.forEach((row, r) =>
        row.forEach((_, c) => {
          if (!given.has(`${r},${c}`)) solved.add(`${r},${c}`);
        })
      );

      setGrid(data.grid.map((row) => row.map((v) => String(v))));
      setGivenCells(given);
      setSolvedCells(solved);
    } catch {
      setError('Не вдалося зʼєднатися з сервером.');
    } finally {
      setLoading(false);
    }
  };

  const handleClearAll = () => {
    setGrid(EMPTY_GRID());
    setGivenCells(new Set());
    setSolvedCells(new Set());
    setSelectedCell(null);
    setError(null);
  };

  const numpadDisabled = !selectedCell || isSelectedGiven;

  return (
    <div style={styles.container}>
      <h1 style={styles.title}>Судоку Solver</h1>

      <div style={styles.board}>
        {grid.map((row, r) =>
          row.map((value, c) => {
            const key = `${r},${c}`;
            const isGiven = givenCells.has(key);
            const isSolved = solvedCells.has(key);
            const isConflict = conflicts.has(key);
            const blockRight = c === 2 || c === 5;
            const blockBottom = r === 2 || r === 5;
            const isSelected = selectedCell === key;

            return (
              <input
                key={key}
                id={`cell-${r}-${c}`}
                type="text"
                inputMode="numeric"
                value={value}
                readOnly={isGiven}
                onChange={(e) => handleChange(r, c, e.target.value)}
                onKeyDown={(e) => handleKeyDown(e, r, c)}
                onFocus={() => setSelectedCell(key)}
                onBlur={() => setSelectedCell(null)}
                style={{
                  ...styles.cell,
                  ...(isGiven ? styles.cellGiven : {}),
                  ...(isSolved ? styles.cellSolved : {}),
                  ...(isConflict ? styles.cellConflict : {}),
                  ...(isSelected ? styles.cellSelected : {}),
                  borderRight: blockRight ? '2px solid #334155' : '1px solid #94a3b8',
                  borderBottom: blockBottom ? '2px solid #334155' : '1px solid #94a3b8',
                  caretColor: isSelected ? '#2563eb' : 'transparent',
                }}
              />
            );
          })
        )}
      </div>

      <div style={styles.numpad}>
        {[1, 2, 3, 4, 5, 6, 7, 8, 9].map((d) => (
          <button
            key={d}
            onMouseDown={(e) => e.preventDefault()}
            onClick={() => handleNumpadDigit(d)}
            disabled={numpadDisabled}
            style={{
              ...styles.numpadBtn,
              ...(numpadDisabled ? styles.numpadBtnDisabled : {}),
            }}
          >
            {d}
          </button>
        ))}
        <button
          onMouseDown={(e) => e.preventDefault()}
          onClick={handleClearCell}
          disabled={numpadDisabled}
          style={{
            ...styles.numpadBtn,
            ...styles.numpadBtnErase,
            ...(numpadDisabled ? styles.numpadBtnDisabled : {}),
          }}
        >
          ⌫
        </button>
      </div>

      {error && <div style={styles.error}>{error}</div>}

      <div style={styles.buttons}>
        <button
          onClick={handleSolve}
          disabled={loading}
          style={{ ...styles.btn, ...styles.btnPrimary, ...(loading ? styles.btnDisabled : {}) }}
        >
          {loading ? (
            <>
              <span style={styles.spinner} /> Розв'язання...
            </>
          ) : (
            'Розв\'язати'
          )}
        </button>
        <button onClick={handleClearAll} style={{ ...styles.btn, ...styles.btnSecondary }}>
          Очистити все
        </button>
      </div>

      <div style={styles.legend}>
        <span style={styles.legendItem}>
          <span style={{ ...styles.legendDot, background: '#dbeafe' }} /> Введені цифри
        </span>
        <span style={styles.legendItem}>
          <span style={{ ...styles.legendDot, background: '#dcfce7' }} /> Знайдені рішення
        </span>
        <span style={styles.legendItem}>
          <span style={{ ...styles.legendDot, background: '#fee2e2' }} /> Конфлікти
        </span>
      </div>
    </div>
  );
}

const styles = {
  container: {
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    gap: 24,
    padding: 32,
  },
  title: {
    margin: 0,
    fontSize: 28,
    fontWeight: 700,
    color: '#1e293b',
    letterSpacing: '-0.5px',
  },
  board: {
    display: 'grid',
    gridTemplateColumns: 'repeat(9, 52px)',
    gridTemplateRows: 'repeat(9, 52px)',
    border: '2px solid #1e293b',
    borderRadius: 4,
    overflow: 'hidden',
    boxShadow: '0 4px 20px rgba(0,0,0,0.12)',
  },
  cell: {
    width: 52,
    height: 52,
    border: 'none',
    borderRight: '1px solid #94a3b8',
    borderBottom: '1px solid #94a3b8',
    textAlign: 'center',
    fontSize: 20,
    fontWeight: 600,
    color: '#1e293b',
    background: '#ffffff',
    outline: 'none',
    cursor: 'text',
    caretColor: 'transparent',
    transition: 'background 0.15s, box-shadow 0.1s',
  },
  cellSelected: {
    background: '#eff6ff',
    boxShadow: 'inset 0 0 0 2px #2563eb',
    zIndex: 1,
    position: 'relative',
  },
  cellGiven: {
    background: '#dbeafe',
    color: '#1d4ed8',
  },
  cellSolved: {
    background: '#dcfce7',
    color: '#15803d',
  },
  cellConflict: {
    background: '#fee2e2',
    color: '#dc2626',
  },
  numpad: {
    display: 'grid',
    gridTemplateColumns: 'repeat(5, 60px)',
    gap: 8,
  },
  numpadBtn: {
    width: 60,
    height: 60,
    fontSize: 24,
    fontWeight: 600,
    border: '1px solid #cbd5e1',
    borderRadius: 8,
    background: '#f8fafc',
    color: '#1e293b',
    cursor: 'pointer',
    transition: 'background 0.1s, border-color 0.1s',
  },
  numpadBtnErase: {
    color: '#dc2626',
    borderColor: '#fca5a5',
    background: '#fff5f5',
  },
  numpadBtnDisabled: {
    opacity: 0.35,
    cursor: 'not-allowed',
  },
  error: {
    padding: '10px 20px',
    background: '#fee2e2',
    color: '#dc2626',
    borderRadius: 8,
    fontSize: 14,
    fontWeight: 500,
    maxWidth: 500,
    textAlign: 'center',
  },
  buttons: {
    display: 'flex',
    gap: 12,
  },
  btn: {
    padding: '12px 28px',
    fontSize: 15,
    fontWeight: 600,
    border: 'none',
    borderRadius: 8,
    cursor: 'pointer',
    display: 'flex',
    alignItems: 'center',
    gap: 8,
    transition: 'opacity 0.15s, transform 0.1s',
  },
  btnPrimary: {
    background: '#2563eb',
    color: '#fff',
  },
  btnSecondary: {
    background: '#e2e8f0',
    color: '#475569',
  },
  btnDisabled: {
    opacity: 0.7,
    cursor: 'not-allowed',
  },
  spinner: {
    display: 'inline-block',
    width: 14,
    height: 14,
    border: '2px solid rgba(255,255,255,0.4)',
    borderTopColor: '#fff',
    borderRadius: '50%',
    animation: 'spin 0.7s linear infinite',
  },
  legend: {
    display: 'flex',
    gap: 20,
    fontSize: 13,
    color: '#64748b',
  },
  legendItem: {
    display: 'flex',
    alignItems: 'center',
    gap: 6,
  },
  legendDot: {
    width: 14,
    height: 14,
    borderRadius: 3,
    border: '1px solid #cbd5e1',
    display: 'inline-block',
  },
};
