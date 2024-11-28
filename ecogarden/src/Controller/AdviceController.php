<?php

namespace App\Controller;
use App\Repository\AdviceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class AdviceController extends AbstractController
{
    #[Route('/advicenoauth', name: 'app_advicenoauth', methods: ['GET'])]
    public function index(AdviceRepository $adviceRepository): JsonResponse
    {
         $advices = $adviceRepository->findAll();

         $data = [];
         foreach ($advices as $advice) {
             $data[] = [
                 'id' => $advice->getId(),
                 'text' => $advice->getText(),
                 'months' => $advice->getMonths(),
             ];
         }

         return new JsonResponse($data);

     
    }
}
