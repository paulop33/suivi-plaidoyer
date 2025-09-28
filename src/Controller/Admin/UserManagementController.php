<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Association;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

#[IsGranted('ROLE_SUPER_ADMIN')]
class UserManagementController extends AbstractDashboardController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Gestion des utilisateurs')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Retour au dashboard', 'fa fa-arrow-left', 'admin');
        yield MenuItem::section('Gestion des utilisateurs');
        yield MenuItem::linkToRoute('Liste des utilisateurs', 'fas fa-list', 'admin_users_list');
        yield MenuItem::linkToRoute('Créer un utilisateur', 'fas fa-plus', 'admin_users_new');
    }

    #[Route('/admin/users', name: 'admin_users_list')]
    public function list(): Response
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();

        return $this->render('admin/users/list.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/users/new', name: 'admin_users_new')]
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createUserForm($user, true);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('admin/users/form.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'title' => 'Créer un utilisateur',
        ]);
    }

    #[Route('/admin/users/{id}/edit', name: 'admin_users_edit')]
    public function edit(User $user, Request $request): Response
    {
        $form = $this->createUserForm($user, false);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe seulement si un nouveau est fourni
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès.');
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('admin/users/form.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'title' => 'Modifier l\'utilisateur',
        ]);
    }

    #[Route('/admin/users/{id}/delete', name: 'admin_users_delete', methods: ['POST'])]
    public function delete(User $user, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_users_list');
    }

    private function createUserForm(User $user, bool $isNew): \Symfony\Component\Form\FormInterface
    {
        $builder = $this->createFormBuilder($user, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'user_form',
        ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Association' => User::ROLE_ASSOCIATION,
                    'Super Admin' => User::ROLE_SUPER_ADMIN,
                ],
                'multiple' => true,
                'expanded' => true,
                'required' => true,
            ])
            ->add('association', EntityType::class, [
                'class' => Association::class,
                'choice_label' => 'name',
                'label' => 'Association',
                'required' => false,
                'placeholder' => 'Sélectionner une association',
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
            ])
            ->add('password', PasswordType::class, [
                'label' => $isNew ? 'Mot de passe' : 'Nouveau mot de passe',
                'required' => $isNew,
                'mapped' => false,
                'help' => $isNew ? '' : 'Laissez vide pour conserver le mot de passe actuel',
            ])
            ->add('submit', SubmitType::class, [
                'label' => $isNew ? 'Créer' : 'Modifier',
                'attr' => ['class' => 'btn btn-primary'],
            ]);

        return $builder->getForm();
    }
}
