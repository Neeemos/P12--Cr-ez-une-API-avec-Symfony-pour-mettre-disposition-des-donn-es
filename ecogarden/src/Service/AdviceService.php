<?php

namespace App\Service;

use App\Repository\AdviceRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdviceService
{
    private AdviceRepository $adviceRepository;

    public function __construct(AdviceRepository $adviceRepository)
    {
        $this->adviceRepository = $adviceRepository;
    }

    public function getAdviceForMonth(int $month): array|JsonResponse
    {
        // Vérification de la validité du mois
        if ($month < 1 || $month > 12) {
            throw new \InvalidArgumentException('Mois invalide');
        }

        // Rechercher tous les conseils
        $advices = $this->adviceRepository->findByMonth($month);

        if (empty($advices)) {
            return new JsonResponse([], 200); // Tableau vide si aucun conseil trouvé
        }
    

        // Préparer les données formatées
        return array_map(function ($item) {
            return $item->getText();
        }, $advices);
    }
}
