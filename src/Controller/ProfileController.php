<?php

namespace App\Controller;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractDashboardController
{
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Mon Profil')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Retour au dashboard', 'fa fa-arrow-left', 'admin');
        yield MenuItem::section('Mon compte');
        yield MenuItem::linkToRoute('Mon profil', 'fas fa-user', 'app_profile');
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            yield MenuItem::linkToRoute('Modifier mon profil', 'fas fa-edit', 'admin_users_edit', ['id' => $this->getUser()->getId()]);
        }
    }

    #[Route('/admin/profile', name: 'app_profile')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('admin/profile.html.twig', [
            'user' => $user,
        ]);
    }
}
