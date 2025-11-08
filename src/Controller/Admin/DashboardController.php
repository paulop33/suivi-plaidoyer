<?php

namespace App\Controller\Admin;

use App\Entity\Association;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\Commitment;
use App\Entity\CandidateList;
use App\Entity\ElectedList;
use App\Entity\ProgressUpdate;
use App\Entity\Proposition;
use App\Entity\Specificity;
use App\Entity\SpecificExpectation;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private ChartBuilderInterface $chartBuilder,
    ) {
    }

    public function index(): Response
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'label' => 'My First dataset',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [0, 10, 5, 2, 20, 30, 45],
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);

        return $this->render('admin/home-dashboard.html.twig', [
            'chart' => $chart,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Suivi Plaidoyer')
            ->setFaviconPath('favicon.ico')
            ->setTranslationDomain('admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        // Menu principal accessible à tous les utilisateurs connectés
        yield MenuItem::section('Gestion des données');
        yield MenuItem::linkToCrud('Associations', 'fas fa-users', Association::class);
        yield MenuItem::linkToCrud('Catégories', 'fas fa-tags', Category::class);
        yield MenuItem::linkToCrud('Propositions', 'fas fa-list', Proposition::class);
        yield MenuItem::linkToCrud('Spécificités', 'fas fa-map-marker-alt', Specificity::class);
        yield MenuItem::linkToCrud('Attentes spécifiques', 'fas fa-bullseye', SpecificExpectation::class);
        yield MenuItem::linkToCrud('Villes', 'fas fa-city', City::class);
        yield MenuItem::linkToCrud('Listes', 'fas fa-list', CandidateList::class);
        yield MenuItem::linkToCrud('Engagements', 'fas fa-check', Commitment::class);

        // Menu suivi post-élections
        yield MenuItem::section('Suivi post-élections');
        yield MenuItem::linkToCrud('Listes élues', 'fas fa-trophy', ElectedList::class);
        yield MenuItem::linkToCrud('Suivi des avancées', 'fas fa-chart-line', ProgressUpdate::class);

        // Menu administration accessible uniquement aux super admins
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            yield MenuItem::section('Administration');
            yield MenuItem::linkToRoute('Utilisateurs', 'fas fa-user-cog', 'admin_users_list');
        }

        // Menu utilisateur
        yield MenuItem::section('Mon compte');
        yield MenuItem::linkToRoute('Profil', 'fas fa-user', 'app_profile')
            ->setPermission('ROLE_USER');
        yield MenuItem::linkToRoute('Déconnexion', 'fas fa-sign-out-alt', 'app_logout');

        // Lien vers le site public
        yield MenuItem::section('Navigation');
        yield MenuItem::linkToUrl('Site public', 'fas fa-external-link-alt', '/');
    }
}
