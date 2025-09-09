<?php

namespace App\DataFixtures;

use App\Entity\Association;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\Proposition;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer les 28 communes de Bordeaux Métropole
        $cities = $this->createCities($manager);

        // Créer les associations MAMMA
        $associations = $this->createAssociations($manager);

        // Créer des catégories et propositions
        $this->createCategoriesAndPropositions($manager);

        // Associer quelques associations aux villes
        $this->associateAssociationsWithCities($cities, $associations, $manager);

        $manager->flush();
    }

    private function createCities(ObjectManager $manager): array
    {
        $bordeauxMetropoleCities = [
            'Ambarès-et-Lagrave',
            'Ambès',
            'Artigues-près-Bordeaux',
            'Bassens',
            'Bègles',
            'Blanquefort',
            'Bordeaux',
            'Bouliac',
            'Bruges',
            'Carbon-Blanc',
            'Cenon',
            'Eysines',
            'Floirac',
            'Gradignan',
            'Le Bouscat',
            'Le Haillan',
            'Le Taillan-Médoc',
            'Lormont',
            'Martignas-sur-Jalle',
            'Mérignac',
            'Parempuyre',
            'Pessac',
            'Saint-Aubin-de-Médoc',
            'Saint-Louis-de-Montferrand',
            'Saint-Médard-en-Jalles',
            'Saint-Vincent-de-Paul',
            'Talence',
            'Villenave-d\'Ornon'
        ];

        $cities = [];
        foreach ($bordeauxMetropoleCities as $cityName) {
            $city = new City();
            $city->setName($cityName);
            $manager->persist($city);
            $cities[] = $city;
        }

        return $cities;
    }

    private function createAssociations(ObjectManager $manager): array
    {
        $mammaAssociations = [
            [
                'name' => 'MAMMA Bordeaux Centre',
                'color' => '#FF6B6B',
                'image' => 'https://via.placeholder.com/150/FF6B6B/FFFFFF?text=MAMMA+BC'
            ],
            [
                'name' => 'MAMMA Rive Droite',
                'color' => '#4ECDC4',
                'image' => 'https://via.placeholder.com/150/4ECDC4/FFFFFF?text=MAMMA+RD'
            ],
            [
                'name' => 'MAMMA Rive Gauche',
                'color' => '#45B7D1',
                'image' => 'https://via.placeholder.com/150/45B7D1/FFFFFF?text=MAMMA+RG'
            ],
            [
                'name' => 'MAMMA Pessac-Gradignan',
                'color' => '#96CEB4',
                'image' => 'https://via.placeholder.com/150/96CEB4/FFFFFF?text=MAMMA+PG'
            ],
            [
                'name' => 'MAMMA Mérignac',
                'color' => '#FFEAA7',
                'image' => 'https://via.placeholder.com/150/FFEAA7/000000?text=MAMMA+M'
            ],
            [
                'name' => 'MAMMA Blanquefort-Parempuyre',
                'color' => '#DDA0DD',
                'image' => 'https://via.placeholder.com/150/DDA0DD/FFFFFF?text=MAMMA+BP'
            ],
            [
                'name' => 'MAMMA Eysines-Le Haillan',
                'color' => '#98D8C8',
                'image' => 'https://via.placeholder.com/150/98D8C8/FFFFFF?text=MAMMA+EH'
            ],
            [
                'name' => 'MAMMA Saint-Médard',
                'color' => '#F7DC6F',
                'image' => 'https://via.placeholder.com/150/F7DC6F/000000?text=MAMMA+SM'
            ]
        ];

        $associations = [];
        foreach ($mammaAssociations as $associationData) {
            $association = new Association();
            $association->setName($associationData['name']);
            $association->setColor($associationData['color']);
            $association->setImage($associationData['image']);
            $manager->persist($association);
            $associations[] = $association;
        }

        return $associations;
    }

    private function createCategoriesAndPropositions(ObjectManager $manager): void
    {
        $categoriesData = [
            [
                'name' => 'Mobilité',
                'bareme' => 100,
                'propositions' => [
                    ['name' => 'Développer les pistes cyclables', 'bareme' => 20],
                    ['name' => 'Améliorer les transports en commun', 'bareme' => 25],
                    ['name' => 'Créer des zones piétonnes', 'bareme' => 15],
                    ['name' => 'Installer des bornes de recharge électrique', 'bareme' => 20],
                    ['name' => 'Développer le covoiturage', 'bareme' => 20]
                ]
            ],
            [
                'name' => 'Environnement',
                'bareme' => 100,
                'propositions' => [
                    ['name' => 'Créer des espaces verts', 'bareme' => 30],
                    ['name' => 'Installer des composteurs collectifs', 'bareme' => 15],
                    ['name' => 'Développer l\'agriculture urbaine', 'bareme' => 25],
                    ['name' => 'Réduire la pollution lumineuse', 'bareme' => 15],
                    ['name' => 'Protéger la biodiversité', 'bareme' => 15]
                ]
            ],
            [
                'name' => 'Social et Solidarité',
                'bareme' => 100,
                'propositions' => [
                    ['name' => 'Créer des centres sociaux', 'bareme' => 25],
                    ['name' => 'Développer l\'aide alimentaire', 'bareme' => 20],
                    ['name' => 'Soutenir les personnes âgées', 'bareme' => 25],
                    ['name' => 'Accompagner les familles', 'bareme' => 15],
                    ['name' => 'Favoriser l\'insertion professionnelle', 'bareme' => 15]
                ]
            ]
        ];

        foreach ($categoriesData as $categoryData) {
            $category = new Category();
            $category->setName($categoryData['name']);
            $category->setBareme($categoryData['bareme']);
            $manager->persist($category);

            foreach ($categoryData['propositions'] as $propositionData) {
                $proposition = new Proposition();
                $proposition->setName($propositionData['name']);
                $proposition->setBareme($propositionData['bareme']);
                $proposition->setCategory($category);
                $manager->persist($proposition);
            }
        }
    }

    private function associateAssociationsWithCities(array $cities, array $associations, ObjectManager $manager): void
    {
        // Associer quelques associations avec des villes spécifiques
        $cityAssociationMap = [
            'Bordeaux' => ['MAMMA Bordeaux Centre'],
            'Pessac' => ['MAMMA Pessac-Gradignan'],
            'Gradignan' => ['MAMMA Pessac-Gradignan'],
            'Mérignac' => ['MAMMA Mérignac'],
            'Blanquefort' => ['MAMMA Blanquefort-Parempuyre'],
            'Parempuyre' => ['MAMMA Blanquefort-Parempuyre'],
            'Eysines' => ['MAMMA Eysines-Le Haillan'],
            'Le Haillan' => ['MAMMA Eysines-Le Haillan'],
            'Saint-Médard-en-Jalles' => ['MAMMA Saint-Médard'],
            'Cenon' => ['MAMMA Rive Droite'],
            'Floirac' => ['MAMMA Rive Droite'],
            'Lormont' => ['MAMMA Rive Droite'],
            'Bègles' => ['MAMMA Rive Gauche'],
            'Talence' => ['MAMMA Rive Gauche'],
            'Villenave-d\'Ornon' => ['MAMMA Rive Gauche']
        ];

        foreach ($cities as $city) {
            if (isset($cityAssociationMap[$city->getName()])) {
                foreach ($cityAssociationMap[$city->getName()] as $associationName) {
                    foreach ($associations as $association) {
                        if ($association->getName() === $associationName) {
                            $city->addReferenteAssociation($association);
                            break;
                        }
                    }
                }
            }
        }
    }
}
