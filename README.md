# ♟️ Chess PGN Parser for PHP ♟️

A lightweight PHP library to parse and export chess game notations:

- PGN (Portable Game Notation) full game parsing (tags, moves, results, comments, NAGs, variations)
- SAN (Standard Algebraic Notation) individual move parsing / exporting
- FEN (Forsyth–Edwards Notation) position parsing / exporting

Built with modern PHP 8.4 features (Enums, typed properties) and released under the MIT license.

## Table of Contents

1. [Installation](#1-installation)
2. [Quick Start](#2-quick-start)
3. [Features](#3-features)
4. [Usage Guide](#4-usage-guide)
   - [Parsing a PGN into a Game](#parsing-a-pgn-into-a-game)
   - [Building a Game Programmatically](#building-a-game-programmatically)
   - [Iterating Moves & Variations](#iterating-moves--variations)
   - [Exporting Back to PGN](#exporting-back-to-pgn)
   - [Parsing SAN Moves](#parsing-san-moves)
   - [Parsing / Exporting FEN Positions](#parsing--exporting-fen-positions)
   - [Applying Moves (MoveApplier)](#applying-moves-moveapplier)
   - [Validation (PositionValidator & GameValidator)](#validation-positionvalidator--gamevalidator)
5. [Data Model Overview](#5-data-model-overview)
6. [Enums Reference](#6-enums-reference)
7. [Testing & Development](#7-testing--development)
8. [License](#8-license)

## 1. Installation

Requires PHP >= 8.4.

```bash
composer require cmuset/pgn-parser
```

## 2. Quick Start

```php
use Cmuset\PgnParser\Model\Game;

$pgn = file_get_contents('path/to/game.pgn');
$game = Game::fromPGN($pgn);

echo "Result: " . ($game->getResult()?->value ?? '*') . PHP_EOL; // e.g. Result: 1-0

foreach ($game->getMainLine() as $key => $node) {
    $move = $node->getMove();
    echo $key . ' ' . $move->getSAN(); // e.g. "1. e4" or "1... e5"
    if ($comment = $node->getAfterMoveComment()) {
        echo ' {' . $comment . '}'; // e.g. " {Good central move}"
    }
}
```

## 3. Features

- Parse a single PGN string into a structured `Game` object:
  - Tag pairs (e.g. `Event`, `Site`, `Date`, custom tags)
  - Result (from tag or trailing token)
  - Move text with: move numbers, SAN moves, comments `{}`, semicolon comments `;`, NAGs (`$1`, `$2`, ...), nested variations `( ... )`
- Parse SAN strings (`e4`, `Nf3`, `O-O`, `exd5`, `c8=Q+`, `Rxe5#`, etc.) into a `Move` object
- Parse FEN strings into a `Position` (piece placement, side to move, castling rights, en passant target, halfmove and fullmove counters)
- Export back to:
  - PGN (`GameExporter`)
  - SAN (`MoveExporter`) – implicitly via `Move::getSAN()`
  - FEN (`PositionExporter`) – implicitly via `Position::getFEN()`
- Access structured comments (before / after a move) and NAGs
- Access variations as arrays of `MoveNode` lines
- Apply moves to positions with `MoveApplier` / `Position::applyMove()`:
  - Handles castling (incl. rights update), en passant (capture + target square), promotions, capture detection, halfmove/fullmove counters, and side-to-move toggling
  - Throws `MoveApplyingException` with `MoveViolationEnum` and, when relevant, embedded `PositionViolationEnum[]`
- Validate positions and games:
  - `PositionValidator` checks king presence/uniqueness, and whether the side to move is in check, etc.
  - `GameValidator` simulates the main line and variations, returning an array of violations (`ViolationEnumInterface[]`) for the first failing move encountered

## 4. Usage Guide

### Parsing a PGN into a `Game`

```php
use Cmuset\PgnParser\Parser\PGNParser;

$parser = PGNParser::create();
$game = $parser->parse($rawPgnString);
```

Or shorthand:

```php
$game = Game::fromPGN($rawPgnString);
```
### Building a Game Programmatically

```php
use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Model\MoveNode;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Enum\ResultEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Parser\PGNParser;

$game = new Game();
$game->setInitialPosition(Position::fromFEN(PGNParser::INITIAL_FEN));
$game->setTag('Event', 'Casual Game');
$game->setTag('Site', 'Local');
$game->setTag('Result', '1-0');
$game->setResult(ResultEnum::WHITE_WIN);

$node1 = new MoveNode();
$node1->setMove(Move::fromSAN('e4', ColorEnum::WHITE));

$node2 = new MoveNode();
$node2->setMove(Move::fromSAN('e5', ColorEnum::BLACK));

$game->addMoveNodes($node1, $node2);

echo $game->getPGN(); // Example output:
// [Event "Casual Game"]\n[Site "Local"]\n[Result "1-0"]\n\n1. e4 e5 1-0
```

### Iterating Moves & Variations

`Game::getMainLine()` returns an array keyed by move number with `.` or `...` (e.g. `"1."`, `"1..."`). Values are `MoveNode` objects.

```php
/** @var \Cmuset\PgnParser\Model\MoveNode $node */
foreach ($game->getMainLine() as $key => $node) {
    $san = $node->getMove()->getSAN();
    echo $key . ' ' . $san . PHP_EOL; // e.g. "5. Nf3" or "12... Qxd4+"

    /** @var \Cmuset\PgnParser\Model\MoveNode $variationLine */
    foreach ($node->getVariations() as $i => $variationLine) {
        echo "(Variation #" . $i . ")\n"; // e.g. "(Variation #1)"
        foreach ($variationLine as $vNode) {
            echo $vNode->getMoveNumber() . ' ' . $vNode->getColor()->name . ' ' . $vNode->getMove()->getSAN(); // e.g. "6 WHITE d5"
        }
    }
}
```

### Exporting Back to PGN

```php
$pgnOut = $game->getPGN();
file_put_contents('out.pgn', $pgnOut); // Writes the full PGN text (tags + moves + result) to out.pgn
```

### Parsing SAN Moves

```php
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Enum\ColorEnum;

$move = Move::fromSAN('Nf3', ColorEnum::WHITE);
echo $move->getSAN(); // Nf3
```

Available flags on `Move` include: piece, origin disambiguation (`squareFrom`, `fileFrom`, `rowFrom`), destination (`to`), capture, check, checkmate, castling, promotion, annotation (!!, !?, ?!, ??, !, ?).

### Parsing / Exporting FEN Positions

```php
use Cmuset\PgnParser\Model\Position;

$position = Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');

// Read a square
use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Enum\PieceEnum;

$piece = $position->getPieceAt(CoordinatesEnum::E2); // e.g. white pawn enum instance

// Modify then export
$position->setPieceAt(CoordinatesEnum::E4, PieceEnum::WHITE_PAWN); // places a white pawn on e4 (illustrative)
$position->setPieceAt(CoordinatesEnum::E2, null); // removes piece from e2
echo $position->getFEN(); // e.g. "rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR w KQkq - 0 1"
```

### Applying Moves (MoveApplier)

Apply a SAN move to a `Position`, with full rules handling (castling rights, en passant, promotion, counters):

```php
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Enum\ColorEnum;
use Cmuset\PgnParser\Parser\PGNParser;
use Cmuset\PgnParser\Exception\MoveApplyingException;

$pos = Position::fromFEN(PGNParser::INITIAL_FEN);
try {
    $pos = $pos->applyMove(Move::fromSAN('e4', ColorEnum::WHITE));
    // $pos->getSideToMove() is now BLACK; halfmove/fullmove counters updated; castling rights maintained
} catch (MoveApplyingException $e) {
    // Inspect the violation(s)
    $moveViolation = $e->getMoveViolation(); // MoveViolationEnum|null
    $positionViolations = $e->getPositionViolations(); // PositionViolationEnum[]
}

// Generate legal moves for the side to move
$legalMoves = $pos->getLegalMoves(); // array of Move
```

### Validation (PositionValidator & GameValidator)

Validate a single position or a full game (main line + variations):

```php
use Cmuset\PgnParser\Validator\PositionValidator;
use Cmuset\PgnParser\Validator\GameValidator;
use Cmuset\PgnParser\Model\Game;

$posViolations = (new PositionValidator())->validate($pos); // PositionViolationEnum[]

$game = Game::fromPGN($somePgn);
$violation = (new GameValidator())->validate($game); // GameViolation object

if ($violation === null) {
    // The game main line and its variations are valid
}
```

Common position checks include: presence of both kings, uniqueness (no duplicates), king-in-check for the side to move, and basic legality context for derived positions.

## 5. Data Model Overview

| Class      | Purpose                                                                |
|------------|------------------------------------------------------------------------|
| `Game`     | Represents a full PGN game (tags, initial position, main line, result) |
| `MoveNode` | A node in the move tree (move + comments + NAGs + variations)          |
| `Move`     | A parsed SAN move (piece, destination, capture, promotion, etc.)       |
| `Position` | A FEN-described board state with piece placement and metadata          |
| `Square`   | A board square container (enum + piece)                                |

Exporters (`GameExporter`, `MoveExporter`, `PositionExporter`) turn objects back into textual notation.

Parsers (`PGNParser`, `SANParser`, `FENParser`) transform strings into structured objects.

## 6. Enums Reference

- `ColorEnum`: WHITE / BLACK
- `PieceEnum`: Typed by color & piece
- `CoordinatesEnum`: All 64 squares (`a1` .. `h8`)
- `CastlingEnum`: Encodes side + direction
- `ResultEnum`: `1-0`, `0-1`, `1/2-1/2`, `*`
- `MoveViolationEnum`: Move-level violations (e.g., wrong color to move, no piece to capture, not-a-check, castling not allowed, etc.)
- `PositionViolationEnum`: Position-level violations (e.g., missing king, multiple kings, king in check, etc.)

## 7. Testing & Development

Clone the repository and install dev dependencies:

```bash
git clone https://github.com/clemuset/pgn-parser.git
cd pgn-parser
composer install
```

Run the test suite:

```bash
vendor/bin/phpunit
```

Static analysis & coding style:

```bash
vendor/bin/phpstan analyze
vendor/bin/php-cs-fixer fix --dry-run
```

## 8. License

MIT License. See `LICENSE`.

---

Contributions welcome: issues, pull requests, and suggestions. If you add a new feature, please include tests.
