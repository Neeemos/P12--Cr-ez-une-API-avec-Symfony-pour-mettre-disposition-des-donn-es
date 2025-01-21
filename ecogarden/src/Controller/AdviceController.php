<?php

namespace App\Controller;
use App\Repository\AdviceRepository;
use App\Entity\Advice;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Log\LoggerInterface;
use App\Repository\UserRepository;
use DateTime;
use App\Service\AdviceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Utils\Validator;
use App\Service\MeteoService;

class AdviceController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private AdviceService $adviceService;

    public function __construct(HttpClientInterface $httpClient, AdviceService $adviceService)
    {
        $this->httpClient = $httpClient;
        $this->adviceService = $adviceService;
    }

    #[Route('/meteo/{city?}', name: 'app_weather', methods: ['GET'])]
    public function weather(?string $city, MeteoService $meteoService): JsonResponse
    {
        try {
            $weatherData = $meteoService->getWeather($city);
            
            return $this->json($weatherData);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Une erreur est survenue : ' . $e->getMessage()], 500);
        }
    }



    #[Route('/conseil/{month}', name: 'app_advice', methods: ['GET'], requirements: ['month' => '\d+'])]
    public function getAdviceByMonth(string $month): JsonResponse
    {
        try {
            $monthInt = intval($month);
            $adviceData = $this->adviceService->getAdviceForMonth($monthInt);

            if (empty($adviceData)) {
                return new JsonResponse(['error' => 'Aucun conseil trouvé'], 404);
            }

            return $this->json($adviceData);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/conseil/', name: 'app_advice_current', methods: ['GET'])]
    public function getCurrentMonthAdvice(): JsonResponse
    {
        try {
            $currentMonth = (new DateTime())->format('n');
            $adviceData = $this->adviceService->getAdviceForMonth((int) $currentMonth);

            if (empty($adviceData)) {
                return new JsonResponse(['error' => 'Aucun conseil trouvé pour le mois actuel'], 404);
            }

            return $this->json($adviceData);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/conseil', name: 'app_advice_add', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function addAdvice(Request $request, EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authChecker): JsonResponse
    {

        // Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);

        $validationError = Validator::isValidAdviceData($data);
        if ($validationError) {
            return new JsonResponse($validationError, 400);
        }

        $months = array_map('intval', $data['month']);
        $validationError = Validator::isValidMonth($months);

        if ($validationError) {
            return new JsonResponse($validationError, 400);
        }

        // Créer un nouvel objet Advice
        $advice = new Advice();
        $advice->setText($data['advice']);
        $advice->setMonths($months);

        $entityManager->persist($advice);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Conseil ajouté avec succès.'], 201);
    }

    #[Route('/conseil/{id}', name: 'app_advice_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateAdvice(
        Advice $advice,
        Request $request,
        AdviceRepository $adviceRepository,
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authChecker
    ): JsonResponse {

        // Décoder les données de la requête
        $data = json_decode($request->getContent(), true);

        $validationError = Validator::isValidAdviceData($data);
        if ($validationError) {
            return new JsonResponse($validationError, 400);
        }

        $months = array_map('intval', $data['month']);
        $validationError = Validator::isValidMonth($months);

        if ($validationError) {
            return new JsonResponse($validationError, 400);
        }

        $advice->setText($data['advice']);
        $advice->setMonths($months);

        // Sauvegarder les modifications
        $entityManager->persist($advice);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Conseil mis à jour avec succès.'], 200);
    }
    #[Route('/conseil/{id}', name: 'app_advice_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteAdvice(
        Advice $advice,
        AdviceRepository $adviceRepository,
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authChecker
    ): JsonResponse {

        $entityManager->remove($advice);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Conseil supprimé avec succès.'], 200);
    }


}


