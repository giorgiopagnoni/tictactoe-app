<?php

namespace App\Service;

use App\Entity\Game;
use App\Entity\Move;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;

class GameService
{
    private array $winningCombinations = [
        [0, 1, 2],
        [3, 4, 5],
        [6, 7, 8],
        [0, 4, 8],
        [2, 4, 6],
        [0, 3, 6],
        [1, 4, 7],
        [2, 5, 8]
    ];

    private GameRepository $gameRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(GameRepository $gameRepository, EntityManagerInterface $entityManager)
    {
        $this->gameRepository = $gameRepository;
        $this->entityManager = $entityManager;
    }

    public function findOpenGameById(string $id): ?Game
    {
        return $this->gameRepository->findOneBy(['id' => $id, 'closedAt' => null]);
    }

    public function createGame(): Game
    {
        $game = new Game();
        $this->entityManager->persist($game);
        $this->entityManager->flush();
        return $game;
    }

    /**
     * @throws \Exception
     */
    public function advanceGame(Game $game, int $player, int $position): array
    {
        $board = $this->buildBoard($game);
        if ($board[$position] !== 0) {
            throw new \Exception('Position already taken');
        }

        $latestMove = $game->getMoves()->last();
        if ($latestMove && $latestMove->getPlayer() === $player) {
            throw new \Exception('Not your turn');
        }

        // update game status
        $board[$position] = $player;
        $winner = $this->hasPlayerWon($board, $player) ? $player : null;
        if ($winner) {
            $game->setWinner($winner);
        }
        if ($winner || $this->isGameOver($board)) {
            $game->setClosedAt(new \DateTimeImmutable());
        }

        // insert move
        $m = new Move();
        $m->setGame($game)
            ->setPlayer($player)
            ->setPosition($position);
        $game->addMove($m);

        $this->entityManager->persist($m);
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        return [
            'board' => $board,
            'winner' => $winner,
            'isGameOver' => $game->isGameOver()
        ];
    }

    private function buildBoard(Game $game): array
    {
        $board = [0, 0, 0, 0, 0, 0, 0, 0, 0];
        foreach ($game->getMoves() as $move) {
            $position = $move->getPosition();
            $player = $move->getPlayer();
            $board[$position] = $player;
        }
        return $board;
    }

    private function hasPlayerWon($board, $player): bool
    {
        foreach ($this->winningCombinations as $combination) {
            if ($board[$combination[0]] === $player &&
                $board[$combination[1]] === $player &&
                $board[$combination[2]] === $player
            ) return true;
        }
        return false;
    }

    private function isGameOver($board): bool
    {
        foreach ($board as $b) {
            if ($b === 0) return false;
        }
        return true;
    }
}