<?php

namespace App\Controller;

use App\Entity\Game;
use App\Service\GameService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TicTacToeController extends AbstractController
{
    #[Route('/health_check', name: 'health_check', methods: 'GET')]
    public function healthCheck(): Response
    {
        return $this->json(null);
    }

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

    #[Route('/game/{gameId}/advance', name: 'game_advance', requirements: ['gameId' => '%routing.uuid%'], methods: 'PUT')]
    public function advanceGame(string             $gameId, Request $request,
                                GameService        $gameService,
                                ValidatorInterface $validator): JsonResponse
    {
        $game = $gameService->findOpenGameById($gameId);
        if (!$game) return new JsonResponse(null, 404);

        $reqParams = json_decode($request->getContent(), true);
        $constraints = new Collection([
            'player' => [
                new Assert\NotBlank(),
                new Assert\Choice([1, 2])
            ],
            'position' => [
                new Assert\NotBlank(),
                new Assert\Range(['min' => 0, 'max' => 8])
            ]
        ]);

        $errors = $validator->validate($reqParams, $constraints);
        if ($errors->count() > 0) {
            $errorMessages = [];
            foreach ($errors as $e) {
                $errorMessages[$e->getPropertyPath()][] = $e->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        try {
            $result = $gameService->advanceGame($game, $reqParams['player'], $reqParams['position']);
        } catch (\Exception $exception) {
            return $this->json(['error' => $exception->getMessage()], 401);
        }

        return $this->json($result);
    }
}
