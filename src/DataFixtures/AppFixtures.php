<?php

namespace App\DataFixtures;

use App\Entity\Association;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\Proposition;
use App\Entity\Specificity;
use App\Entity\SpecificExpectation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer les spécificités
        $specificities = $this->createSpecificities($manager);

        // Créer les 28 communes de Bordeaux Métropole
        $cities = $this->createCities($manager);

        // Créer les associations MAMMA
        $associations = $this->createAssociations($manager);

        // Créer des catégories et propositions
        $propositions = $this->createCategoriesAndPropositions($manager);

        // Associer quelques associations aux villes
        $this->associateAssociationsWithCities($cities, $associations, $manager);

        // Associer les spécificités aux villes
        $this->associateSpecificitiesToCities($cities, $specificities, $manager);

        // Associer les spécificités aux propositions
        $this->associateSpecificitiesToPropositions($propositions, $specificities, $manager);

        $manager->flush();
    }

    private function createSpecificities(ObjectManager $manager): array
    {
        $specificitiesData = [
            [
                'name' => 'Intra-rocade',
                'description' => 'Communes situées à l\'intérieur de la rocade bordelaise'
            ],
            [
                'name' => 'Extra-rocade',
                'description' => 'Communes situées à l\'extérieur de la rocade bordelaise'
            ],
            [
                'name' => 'Centre urbain',
                'description' => 'Zones à forte densité urbaine'
            ],
            [
                'name' => 'Rive gauche',
                'description' => 'Communes situées sur la rive gauche de la Garonne'
            ],
            [
                'name' => 'Rive droite',
                'description' => 'Communes situées sur la rive droite de la Garonne'
            ]
        ];

        $specificities = [];
        foreach ($specificitiesData as $specificityData) {
            $specificity = new Specificity();
            $specificity->setName($specificityData['name']);
            $specificity->setDescription($specificityData['description']);
            $manager->persist($specificity);
            $specificities[$specificityData['name']] = $specificity;
        }

        return $specificities;
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
            $cities[$cityName] = $city;
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

    private function createCategoriesAndPropositions(ObjectManager $manager): array
    {
        $propositions = [];
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
            $category->setImage("https://www.bordeaux-metropole.fr/sites/MET-BXMETRO-DRUPAL/files/styles/node_visuel_xl_x2/public/2023-07/parcours_reve_bruges_velo_barbier.webp");
            $manager->persist($category);

            foreach ($categoryData['propositions'] as $propositionData) {
                $proposition = new Proposition();
                $proposition->setTitle($propositionData['name']);
                $proposition->setBareme($propositionData['bareme']);
                $proposition->setCategory($category);
                $proposition->setImage("https://www.bordeaux-metropole.fr/sites/MET-BXMETRO-DRUPAL/files/styles/node_visuel_xl_x2/public/2023-07/parcours_reve_bruges_velo_barbier.webp");

                // Les attentes seront définies dans associateSpecificitiesToPropositions()
                $manager->persist($proposition);
                $propositions[] = $proposition;
            }
        }

        return $propositions;
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

        foreach ($cities as $cityName => $city) {
            if (isset($cityAssociationMap[$cityName])) {
                foreach ($cityAssociationMap[$cityName] as $associationName) {
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

    private function associateSpecificitiesToCities(array $cities, array $specificities, ObjectManager $manager): void
    {
        // Définir les spécificités pour chaque ville
        $citySpecificityMap = [
            // Intra-rocade + Centre urbain + Rive gauche
            'Bordeaux' => ['Intra-rocade', 'Centre urbain', 'Rive gauche'],
            'Bègles' => ['Intra-rocade', 'Centre urbain', 'Rive gauche'],
            'Talence' => ['Intra-rocade', 'Centre urbain', 'Rive gauche'],
            'Le Bouscat' => ['Intra-rocade', 'Centre urbain', 'Rive gauche'],
            'Bruges' => ['Intra-rocade', 'Rive gauche'],
            'Pessac' => ['Intra-rocade', 'Extra-rocade', 'Rive gauche'],
            'Mérignac' => ['Intra-rocade', 'Extra-rocade', 'Rive gauche'],
            'Eysines' => ['Intra-rocade', 'Extra-rocade', 'Rive gauche'],
            'Villenave-d\'Ornon' => ['Intra-rocade', 'Extra-rocade', 'Rive gauche'],

            // Intra-rocade + Centre urbain + Rive droite
            'Cenon' => ['Intra-rocade', 'Centre urbain', 'Rive droite'],
            'Floirac' => ['Intra-rocade', 'Centre urbain', 'Rive droite'],
            'Lormont' => ['Intra-rocade', 'Centre urbain', 'Rive droite'],

            // Extra-rocade + Rive gauche
            'Gradignan' => ['Extra-rocade', 'Rive gauche'],
            'Le Haillan' => ['Extra-rocade', 'Rive gauche'],
            'Blanquefort' => ['Extra-rocade', 'Rive gauche'],
            'Parempuyre' => ['Extra-rocade', 'Rive gauche'],
            'Saint-Médard-en-Jalles' => ['Extra-rocade', 'Rive gauche'],
            'Martignas-sur-Jalle' => ['Extra-rocade', 'Rive gauche'],
            'Le Taillan-Médoc' => ['Extra-rocade', 'Rive gauche'],
            'Saint-Aubin-de-Médoc' => ['Extra-rocade', 'Rive gauche'],

            // Extra-rocade + Périurbain + Rive droite
            'Artigues-près-Bordeaux' => ['Extra-rocade', 'Rive droite'],
            'Bassens' => ['Extra-rocade', 'Rive droite'],
            'Carbon-Blanc' => ['Extra-rocade', 'Rive droite'],
            'Ambarès-et-Lagrave' => ['Extra-rocade', 'Rive droite'],
            'Ambès' => ['Extra-rocade', 'Rive droite'],
            'Saint-Louis-de-Montferrand' => ['Extra-rocade', 'Rive droite'],
            'Saint-Vincent-de-Paul' => ['Extra-rocade', 'Rive droite'],
            'Bouliac' => ['Extra-rocade', 'Intra-rocade', 'Rive droite'],
        ];

        foreach ($citySpecificityMap as $cityName => $specificityNames) {
            if (isset($cities[$cityName])) {
                $city = $cities[$cityName];
                foreach ($specificityNames as $specificityName) {
                    if (isset($specificities[$specificityName])) {
                        $city->addSpecificity($specificities[$specificityName]);
                    }
                }
            }
        }
    }

    private function associateSpecificitiesToPropositions(array $propositions, array $specificities, ObjectManager $manager): void
    {
        // Exemple : Créer des attentes communes et spécifiques pour quelques propositions

        if (count($propositions) > 0) {
            // Proposition 1 : Attente commune uniquement
            $propositions[0]->setCommonExpectation('Mettre en place le Savoir Rouler À Vélo (SRAV) dans toutes les écoles de la commune');

            // Proposition 2 : Attente commune + attentes spécifiques
            if (count($propositions) > 1) {
                $propositions[1]->setCommonExpectation('Développer les pistes cyclables sur l\'ensemble du territoire');

                // Attente spécifique pour les zones intra-rocade
                $specificExpectation1 = new SpecificExpectation();
                $specificExpectation1->setProposition($propositions[1]);
                $specificExpectation1->setSpecificity($specificities['Intra-rocade']);
                $specificExpectation1->setExpectation('Créer un réseau cyclable continu et sécurisé avec des pistes séparées de la circulation automobile');
                $manager->persist($specificExpectation1);

                // Attente spécifique pour les zones extra-rocade
                $specificExpectation2 = new SpecificExpectation();
                $specificExpectation2->setProposition($propositions[1]);
                $specificExpectation2->setSpecificity($specificities['Extra-rocade']);
                $specificExpectation2->setExpectation('Développer des voies vertes et des liaisons intercommunales sécurisées');
                $manager->persist($specificExpectation2);
            }

            // Proposition 3 : Uniquement des attentes spécifiques (pas d'attente commune)
            if (count($propositions) > 2) {
                // Attente spécifique pour le centre urbain
                $specificExpectation3 = new SpecificExpectation();
                $specificExpectation3->setProposition($propositions[2]);
                $specificExpectation3->setSpecificity($specificities['Centre urbain']);
                $specificExpectation3->setExpectation('Créer des zones piétonnes permanentes et des rues à circulation apaisée');
                $manager->persist($specificExpectation3);

                // Attente spécifique pour le périurbain
                $specificExpectation4 = new SpecificExpectation();
                $specificExpectation4->setProposition($propositions[2]);
                $specificExpectation4->setSpecificity($specificities['Extra-rocade']);
                $specificExpectation4->setExpectation('Aménager les centres-bourgs avec des zones 30 et des traversées sécurisées');
                $manager->persist($specificExpectation4);
            }
        }
    }
}
