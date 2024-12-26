<?php

namespace App\Controller;
use App\Repository\AdviceRepository;
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


class AdviceController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/advicenoauth', name: 'app_advicenoauth', methods: ['GET'])]
    public function index(AdviceRepository $adviceRepository): JsonResponse
    {
        $advices = $adviceRepository->findAll();


        return $this->json($advices);


    }
    #[Route('/api/admin', name: 'app_admintest', methods: ['GET'])]
    public function admintest(AdviceRepository $adviceRepository): JsonResponse
    {
        $advices = $adviceRepository->findAll();


        return $this->json($advices);


    }

    #[Route('/api/weather/{city?}', name: 'app_weather', methods: ['GET'])]
    public function weither(?string $city, CacheInterface $cache, LoggerInterface $logger, UserRepository $userRepository): JsonResponse
    {
        $apiKey = $this->getParameter('openweather_api_key');

        // Vérifier si `city` est null, récupérer la ville de l'utilisateur connecté
        if ($city === null) {
            $user = $this->getUser(); // Nécessite Symfony Security

            $city = $user->getCity();
            if (!$city) {
                return new JsonResponse(['error' => 'Aucune ville associée à cet utilisateur'], 400);
            }
        }

        $url = "https://api.openweathermap.org/data/2.5/weather?q=$city&units=metric&lang=fr&appid=$apiKey";
        $cacheKey = 'weather_' . strtolower($city);

        try {
            $data = $cache->getItem($cacheKey);

            if (!$data->isHit()) {
                $response = $this->httpClient->request('GET', $url);

                if ($response->getStatusCode() !== 200) {
                    throw new \Exception('Impossible de récupérer les données météo, verifier la ville');
                }

                $rawData = $response->toArray();

                $data->set($rawData);
                $data->expiresAfter(3600); // 1 heure
                $cache->save($data);
            } else {
                $rawData = $data->get();
            }

            $filteredData = [
                'country' => $rawData['sys']['country'] ?? null,
                'city' => $rawData['name'] ?? null,
                'temperature' => $rawData['main']['temp'] ?? null,
                'weather_description' => $rawData['weather'][0]['description'] ?? null,
                'wind' => [
                    'speed' => $rawData['wind']['speed'] ?? null,
                    'direction' => $rawData['wind']['deg'] ?? null,
                ],
            ];

            return $this->json($filteredData);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Une erreur est survenue : ' . $e->getMessage()], 500);
        }
    }



    #[Route('/api/advice/{month}', name: 'app_advice', methods: ['GET'], requirements: ['month' => '\d+'])]

    public function getAdviceByMonth(string $month, AdviceRepository $adviceRepository): JsonResponse
    {
        // Conversion du mois en entier pour la recherche
        $monthInt = intval($month);

        // Vérifiez si le mois est valide
        if ($monthInt < 1 || $monthInt > 12) {
            return new JsonResponse(['error' => 'Mois invalide'], 400);
        }

        // Rechercher un conseil dans la base de données
        $advice = $adviceRepository->findAll();

        // Filtrer les conseils où le mois correspond
        $filteredAdvice = array_filter($advice, function ($item) use ($monthInt) {
            return in_array($monthInt, $item->getMonths());
        });

        if (empty($filteredAdvice)) {
            return new JsonResponse(['error' => 'Aucun conseil trouvé'], 404);
        }

        //// Ajouter cache ////

        // Préparer les données à retourner
        $adviceData = array_map(function ($item) {
            return $item->getText() ;
        }, $filteredAdvice);

        return $this->json($adviceData);
    }


    #[Route('/api/advice/current', name: 'app_advice_current', methods: ['GET'])]
    public function getCurrentMonthAdvice(AdviceRepository $adviceRepository): JsonResponse
    {
        // Obtenir le mois actuel
        $currentMonth = (new DateTime())->format('n');

        // Rechercher tous les conseils
        $advice = $adviceRepository->findAll();

        // Filtrer les conseils pour le mois actuel
        $filteredAdvice = array_filter($advice, function ($item) use ($currentMonth) {
            return in_array($currentMonth, $item->getMonths());
        });

        if (empty($filteredAdvice)) {
            return new JsonResponse(['error' => 'Aucun conseil trouvé pour le mois actuel'], 404);
        }

        // Préparer les données à retourner
        $adviceData = array_map(function ($item) {
            return $item->getText();
        }, $filteredAdvice);

        return new JsonResponse($adviceData);
    }

}


