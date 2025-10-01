<?php

namespace App\Controller;

use App\Entity\Category;
use App\Service\CommitmentDataService;
use App\Service\StatisticsService;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/categories')]
class CategoryController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private CommitmentDataService $commitmentDataService,
    ) {
    }


    #[Route('/{id}/{slug}', name: 'app_category_show', requirements: ['id' => '\d+'])]
    public function show(int $id, string $slug): Response
    {
        // Récupérer la catégorie par id et slug
        $category = $this->categoryRepository->findOneByIdAndSlug($id, $slug);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        // Organiser les données des propositions avec leurs signatures
        $propositionData = $this->commitmentDataService->organizePropositionData($category);

        return $this->render('public/category_show.html.twig', [
            'category' => $category,
            'propositionData' => $propositionData,
            'breadcrumbItems' => [
                [
                    'label' => $category->getName(),
                    'icon' => 'fas fa-tag'
                ]
            ],
            'quickActions' => [
                [
                    'url' => $this->generateUrl('app_home'),
                    'label' => 'Retour à l\'accueil',
                    'icon' => 'fas fa-home',
                    'class' => 'btn-outline-secondary'
                ]
            ]
        ]);
    }
}
