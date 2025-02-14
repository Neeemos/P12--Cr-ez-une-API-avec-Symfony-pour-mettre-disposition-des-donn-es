<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\MeteoService;
class MeteoController extends AbstractController
{
/** 
 * recupérer les données meteo
 * 
 * @param ?string $city
 * 
 * @return JsonResponse
 * 
 * @throws \InvalidArgumentException
 */
    #[Route('/meteo/{city?}', name: 'app_weather', methods: ['GET'])]
    public function weather(?string $city, MeteoService $meteoService): JsonResponse
    {
        try {
            $weatherData = $meteoService->getWeather($city);

            return new JsonResponse($weatherData, 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Une erreur est survenue : ' . $e->getMessage()], 500);
        }
    }
}
