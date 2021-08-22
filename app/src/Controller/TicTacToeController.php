<?php

namespace App\Controller;

use App\Entity\Game;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TicTacToeController extends AbstractController
{
    #[Route('/game/start', name: 'game_start', methods: 'GET')]
    public function startGame(EntityManagerInterface $entityManager): JsonResponse
    {
        $game = new Game();
        $entityManager->persist($game);
        $entityManager->flush();

        return $this->json([
            'id' => $game->getId()
        ]);
    }

    #[Route('/game/advance', name: 'game_advance', methods: 'POST')]
    public function advanceGame(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/TicTacToeController.php',
        ]);
    }
}
