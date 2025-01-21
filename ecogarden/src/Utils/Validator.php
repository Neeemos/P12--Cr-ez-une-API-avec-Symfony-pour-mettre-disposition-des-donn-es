<?php

namespace App\Utils;

class Validator
{
    /**
     * Valide un tableau de mois.
     *
     * @param array $months
     * @return array|null Retourne null si valide, ou un tableau d'erreurs.
     */
    public static function isValidMonth(array $months): ?array
    {
        foreach ($months as $month) {
            if ($month < 1 || $month > 12) {
                return ["error" => "Mois invalide : {$month}. Les mois doivent être entre 1 et 12."];
            }
        }
        return null;
    }
    /**
     * Valide la structure des données d'entrée.
     *
     * @param array $data
     * @return array|null Retourne null si valide, ou un tableau d'erreurs.
     */
    public static function isValidAdviceData(array $data): ?array
    {
        if (!$data) {
            return [
                "error" => 'Données invalides : "advice" et "month" sont requis.'
            ];
        }
        if (empty($data['advice']) || empty($data['month']) || !is_array($data['month'])) {
            return [
                "error" => 'Données invalides : "advice" et "month" sont requis, "month" doit être un tableau.'
            ];
        }
        return null;
    }
}
