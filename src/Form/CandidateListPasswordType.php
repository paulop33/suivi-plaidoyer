<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CandidateListPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['is_setting_password']) {
            // Formulaire pour définir un nouveau mot de passe (double saisie)
            $builder
                ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Les mots de passe doivent être identiques.',
                    'first_options' => [
                        'label' => 'Nouveau mot de passe',
                        'attr' => [
                            'placeholder' => 'Saisissez un mot de passe',
                            'class' => 'form-control'
                        ]
                    ],
                    'second_options' => [
                        'label' => 'Confirmer le mot de passe',
                        'attr' => [
                            'placeholder' => 'Confirmez le mot de passe',
                            'class' => 'form-control'
                        ]
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez saisir un mot de passe.',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                            'max' => 255,
                            'maxMessage' => 'Le mot de passe ne peut pas dépasser {{ limit }} caractères.',
                        ]),
                    ],
                ])
                ->add('submit', SubmitType::class, [
                    'label' => 'Définir le mot de passe',
                    'attr' => ['class' => 'btn btn-primary']
                ]);
        } else {
            // Formulaire pour saisir le mot de passe existant
            $builder
                ->add('password', PasswordType::class, [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'placeholder' => 'Saisissez le mot de passe',
                        'class' => 'form-control'
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez saisir le mot de passe.',
                        ]),
                    ],
                ])
                ->add('submit', SubmitType::class, [
                    'label' => 'Accéder aux engagements',
                    'attr' => ['class' => 'btn btn-success']
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'is_setting_password' => false,
        ]);
    }
}
