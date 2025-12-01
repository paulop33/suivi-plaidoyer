<?php

namespace App\Controller;

use App\Service\StatisticsService;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{

    #[Route('/le-collectif', name: 'app_collectif')]
    public function le_collectif(): Response
    {

        return $this->render('page/collectif.html.twig', [
        ]);
    }

    #[Route('/maison-des-livreurs', name: 'app_maison_des_livreurs')]
    public function maisonDesLivreurs(): Response
    {

        return $this->render('page/maison-des-livreurs.html.twig', [
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('page/contact.html.twig', [
        ]);
    }
}
