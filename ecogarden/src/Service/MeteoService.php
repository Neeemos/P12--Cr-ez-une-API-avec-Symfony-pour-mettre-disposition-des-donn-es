<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MeteoService
{
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;
    private LoggerInterface $logger;
    private TokenStorageInterface $tokenStorage;
    private string $apiKey;

    public function __construct(
        HttpClientInterface $httpClient,
        CacheInterface $cache,
        LoggerInterface $logger,
        TokenStorageInterface $tokenStorage,
        string $apiKey
    ) {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;
        $this->apiKey = $apiKey;
    }


    public function getCity(?string $city): string
    {
        // Si aucune ville n'est fournie, récupérer celle associée à l'utilisateur connecté
        if ($city === null) {
            // Récupérer l'utilisateur via le token JWT
            $token = $this->tokenStorage->getToken();
            if ($token && $token->getUser()) {
                // Récupérer la ville associée à l'utilisateur
                $user = $token->getUser();
                // Vérifier si l'utilisateur a la méthode `getCity`
                $city = $user->getCity();
            }

            // Si toujours aucune ville, lancer une exception
            if (!$city) {
                throw new \InvalidArgumentException('Aucune ville n\'a été fournie et aucune ville n\'est associée à cet utilisateur.');
            }
        }

        return trim($city);

    }
    public function getWeather(?string $city): array
    {
        $cityClean = $this->getCity($city);
        $url = sprintf(
            'https://api.openweathermap.org/data/2.5/weather?q=%s&units=metric&lang=fr&appid=%s',
            urlencode($cityClean),
            $this->apiKey
        );

        $cacheKey = 'weather_' . strtolower($cityClean);

        try {
            return $this->cache->get($cacheKey, function () use ($url) {
                $response = $this->httpClient->request('GET', $url);
                if ($response->getStatusCode() !== 200) {
                    throw new \Exception('Impossible de récupérer les données météo. Vérifiez la ville ou l\'API.');
                }

                $data = $response->toArray();

                return [
                    'country' => $data['sys']['country'] ?? null,
                    'city' => $data['name'] ?? null,
                    'temperature' => $data['main']['temp'] ?? null,
                    'weather_description' => $data['weather'][0]['description'] ?? null,
                    'wind' => [
                        'speed' => $data['wind']['speed'] ?? null,
                        'direction' => $data['wind']['deg'] ?? null,
                    ],
                ];
            });
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des données météo : ' . $e->getMessage());
            throw new \RuntimeException('Une erreur est survenue lors de la récupération des données météo.');
        }
    }
}
