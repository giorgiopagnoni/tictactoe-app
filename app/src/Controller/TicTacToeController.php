<?php

namespace App\Controller;

use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;

class TicTacToeController extends AbstractController
{
    #[Route('/health_check', name: 'health_check', methods: 'GET')]
    public function healthCheck(): Response
    {
        return $this->json(null);
    }

    #[Route('/api/game/start', name: 'game_start', methods: 'POST')]
    /**
     * @OA\Response(
     *  response=200,
     *  description="A new game has been created; returns its id",
     *  @OA\JsonContent(
     *      @OA\Property(property="id", type="string", example="1ec03e03-9be8-6e8e-8b6d-d9a473d5056e")
     *  )
     * )
     */
    public function startGame(GameService $gameService): JsonResponse
    {
        $game = $gameService->createGame();
        return $this->json([
            'id' => $game->getId()
        ]);
    }

    #[Route('/api/game/{gameId}/advance', name: 'game_advance', requirements: ['gameId' => '%routing.uuid%'], methods: 'PUT')]
    /**
     * @OA\RequestBody(
     *     @OA\JsonContent(
     *          @OA\Property(property="player", type="integer", minimum=1, maximum=2, example=1),
     *          @OA\Property(property="position", type="integer", minimum=0, maximum=8, example=5)
     *     )
     * )
     * @OA\Response(
     *  response=200,
     *  description="The move was successfull",
     *  @OA\JsonContent(
     *      @OA\Property(property="board", type="array", example="[0,1,0,0,2,1,0,0,0]",
     *              @OA\Items(type="integer"),
     *         ),
     *      @OA\Property(property="winner", type="integer", minimum=1, maximum=2, example=1),
     *      @OA\Property(property="isGameOver", type="boolean")
     *  )
     * )
     * @OA\Response(
     *  response=404,
     *  description="Open game not found",
     * )
     */
    public function advanceGame(string             $gameId,
                                Request            $request,
                                GameService        $gameService,
                                ValidatorInterface $validator): JsonResponse
    {
        $game = $gameService->findOpenGameById($gameId);
        if (!$game) {
            return new JsonResponse(['error' => 'Game not found'], 404);
        }

        $reqParams = json_decode($request->getContent(), true);
        if (!$this->isRequestValid($validator, $reqParams)){
            return new JsonResponse(['error' => 'Invalid position or player'], 400);
        }

        try {
            $result = $gameService->advanceGame($game, $reqParams['player'], $reqParams['position']);
        } catch (\Exception $exception) {
            return $this->json(['error' => $exception->getMessage()], 422);
        }

        return $this->json($result);
    }

    private function isRequestValid(ValidatorInterface $validator, $request): bool
    {
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

        $errors = $validator->validate($request, $constraints);
        return count($errors) === 0;
    }
}
