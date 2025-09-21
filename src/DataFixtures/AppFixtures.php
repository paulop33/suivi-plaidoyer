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
                'name' => 'Vélo-Cité',
                'color' => '#00aeef',
                'image' => 'https://via.placeholder.com/150/FF6B6B/FFFFFF?text=MAMMA+BC'
            ],
            [
                'name' => 'Léon à Vélo',
                'color' => '#0200b0',
                'image' => 'https://via.placeholder.com/150/4ECDC4/FFFFFF?text=MAMMA+RD'
            ],
            [
                'name' => 'Cycles et Manivelles',
                'color' => '#b12214',
                'image' => 'https://via.placeholder.com/150/45B7D1/FFFFFF?text=MAMMA+RG'
            ],
            [
                'name' => 'EtuRecup',
                'color' => '#0b825e',
                'image' => 'https://via.placeholder.com/150/96CEB4/FFFFFF?text=MAMMA+PG'
            ],
            [
                'name' => 'Le Garage Moderne',
                'color' => '#000000',
                'image' => 'https://via.placeholder.com/150/FFEAA7/000000?text=MAMMA+M'
            ],
            [
                'name' => 'La recyclerie Sportive',
                'color' => '#c1d133',
                'image' => 'https://via.placeholder.com/150/DDA0DD/FFFFFF?text=MAMMA+BP'
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
                'name' => 'Circuler dans la Métropole',
                'bareme' => 100,
                'propositions' => [
                    ['name' => 'Poursuivre le développement du ReVE en respectant l’ambition et les standards votés en 2021.', 'bareme' => 20],
                    ['name' => 'Accélérer le déploiement du ReVE pour tenir l’échéance de livraison fixé à 2030', 'bareme' => 25],
                    ['name' => 'Connecter les quartiers au ReVE ou aux grandes pistes de la métropole avec des aménagements cyclables', 'bareme' => 15],
                    ['name' => 'Soutenir la mise en place et la communication pour une métropole à 30km/h', 'bareme' => 20],
                    ['name' => 'Expérimenter et généraliser le M12 tridirectionnel au grand format', 'bareme' => 20],
                    ['name' => 'Transformer le service des modes actifs de Bordeaux Métropole en une direction générale et renforcer ces effectifs', 'bareme' => 20],
                    ['name' => 'Créer et mettre en place une charte de chantier pour prendre en compte les cyclistes lors des travaux de voirie', 'bareme' => 20],
                ]
            ],
            [
                'name' => 'Circuler dans mon Quartier',
                'bareme' => 100,
                'propositions' => [
                    ['name' => 'Privilégier dans l’ordre piéton.nes, vélos, transport en commun', 'bareme' => 30],
                    ['name' => 'repenser les plans de circulation de chaque quartier en limitant le transit motorisé', 'bareme' => 15],
                    ['name' => 'connecter les quartiers au ReVE ou aux grandes pistes de la métropole avec des aménagements cyclables tels que des vélorues.', 'bareme' => 25],
                    ['name' => 'Marquer les entrées de quartier en généralisant les trottoirs et pistes traversants', 'bareme' => 15],
                    ['name' => 'Réduire le stationnement en voirie', 'bareme' => 15],
                    ['name' => 'Éviter les couloirs partagés Bus/vélo', 'bareme' => 15]
                ]
            ],
            [
                'name' => 'Les piétons',
                'bareme' => 100,
                'propositions' => [
                    ['name' => 'Développer des cœurs de quartier piéton ainsi qu’une trame piétonne', 'bareme' => 25],
                    ['name' => 'Proposer une alternative cyclable aux principaux axes piétons', 'bareme' => 20],
                    ['name' => 'Généraliser les trottoirs traversants', 'bareme' => 25],
                    ['name' => 'Réaliser des trottoirs plats', 'bareme' => 15],
                ]
            ],
            [
                'name' => 'Les écoles',
                'bareme' => 100,
                'propositions' => [
                    ['name' => 'Mettre en place le Savoir Rouler À Vélo (SRAV) jusqu’au niveau 3', 'bareme' => 25],
                    ['name' => 'Transformer les rues des écoles en lieux de vie (permanents) sans voiture', 'bareme' => 20],
                    ['name' => 'Inciter et faciliter le déplacements des enfants à vélo ou à pieds', 'bareme' => 25],
                ]
            ],
            [
                'name' => 'Intermodalité et stationnements',
                'bareme' => 100,
                'propositions' => [
                    ['name' => 'Proposer des stationnements sécurisés, simple d’accès, et quantitatif adaptés à chaque gare', 'bareme' => 25],
                    ['name' => 'Développer des parkings sécurisés type Metstation dans les secteurs de stationnement tendus et les pôle d’intérêt majeur', 'bareme' => 20],
                    ['name' => 'Proposer une offre simplifié et unique permettant l’accès à l’ensemble des Metstation de la métropole,', 'bareme' => 25],
                    ['name' => 'Multiplier le nombre de Vélobox dans les quartiers.', 'bareme' => 15],
                ]
            ],
            [
                'name' => 'Guide aménagements Sécurité et confort',
                'bareme' => 100,
                'propositions' => [
                    ['name' => 'Uniformiser de la couleur des aménagements cyclables', 'bareme' => 25],
                    ['name' => 'Ne plus utiliser le stabilisé et le béton pour les trottoirs et pistes cyclables', 'bareme' => 20],
                    ['name' => 'Généraliser la création de trottoirs et pistes traversantes', 'bareme' => 25],
                    ['name' => 'Aménager des trottoirs plats sans “bateaux”', 'bareme' => 15],
                    ['name' => 'Réaliser tous les aménagements cyclables sans bordures', 'bareme' => 15],
                    ['name' => 'Généraliser les arrêt de bus flottant', 'bareme' => 15],
                    ['name' => 'Créer une zone tampon pour mettre à distance du stationnement les bandes/pistes cyclable pour éviter le risque d’emportiérage', 'bareme' => 15],
                    ['name' => 'Sécuriser les sas vélo avec un système de double feu', 'bareme' => 15],
                    ['name' => 'Réaliser tous les giratoires sur le principe des rond point hollandais.', 'bareme' => 15],
                ]
            ],
            [
                'name' => 'Elaborer un Plan vélo',
                'bareme' => 100,
                'propositions' => [
                    ['name' => 'Elaborer un plan vélo à l’échelle de ma commune', 'bareme' => 25],
                    ['name' => 'Respecter la loi LOM (L228-2 du code de l’environnement)', 'bareme' => 20],
                    ['name' => 'Communiquer favorablement et régulièrement sur le vélo', 'bareme' => 25],
                    ['name' => 'Organiser des rencontres', 'bareme' => 15],
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
                $proposition->setTitle($propositionData['name']);
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
