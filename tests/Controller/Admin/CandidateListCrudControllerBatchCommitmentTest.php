<?php

namespace App\Tests\Controller\Admin;

use App\Entity\CandidateList;
use App\Entity\City;
use App\Entity\Commitment;
use App\Entity\Proposition;
use App\Entity\Category;
use App\Enum\CommitmentStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CandidateListCrudControllerBatchCommitmentTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        // Ne pas booter le kernel ici, le faire dans chaque test
    }

    private function getEntityManager(): EntityManagerInterface
    {
        if (!isset($this->entityManager)) {
            $kernel = self::bootKernel();
            $this->entityManager = $kernel->getContainer()
                ->get('doctrine')
                ->getManager();
        }
        return $this->entityManager;
    }

    public function testBatchCommitmentWithStatuses(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();

        // Créer des données de test
        $city = new City();
        $city->setName('Test City');
        $entityManager->persist($city);

        $candidateList = new CandidateList();
        $candidateList->setNameList('Test List');
        $candidateList->setFirstname('John');
        $candidateList->setLastname('Doe');
        $candidateList->setCity($city);
        $entityManager->persist($candidateList);

        $category = new Category();
        $category->setName('Test Category');
        $category->setPosition(1);
        $entityManager->persist($category);

        $proposition1 = new Proposition();
        $proposition1->setTitle('Proposition 1');
        $proposition1->setCategory($category);
        $entityManager->persist($proposition1);

        $proposition2 = new Proposition();
        $proposition2->setTitle('Proposition 2');
        $proposition2->setCategory($category);
        $entityManager->persist($proposition2);

        $entityManager->flush();

        // Simuler une requête POST avec des statuts (plus besoin de propositions[])
        $client->request('POST', '/admin', [
            'crudControllerFqcn' => 'App\\Controller\\Admin\\CandidateListCrudController',
            'crudAction' => 'batchCommitment',
            'entityId' => $candidateList->getId(),
        ], [], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ], http_build_query([
            'proposition_status' => [
                $proposition1->getId() => 'accepted',
                $proposition2->getId() => 'refused',
            ],
            'proposition_comments' => [
                $proposition1->getId() => 'Commentaire pour proposition acceptée',
                $proposition2->getId() => 'Commentaire pour proposition refusée',
            ],
            'global_comment' => 'Commentaire global de test'
        ]));

        // Vérifier que les engagements ont été créés avec les bons statuts
        $entityManager->refresh($candidateList);

        $commitments = $candidateList->getCommitments();
        $this->assertCount(2, $commitments);

        foreach ($commitments as $commitment) {
            if ($commitment->getProposition()->getId() === $proposition1->getId()) {
                $this->assertEquals(CommitmentStatus::ACCEPTED, $commitment->getStatus());
                $this->assertEquals('Commentaire pour proposition acceptée', $commitment->getCommentCandidateList());
            } elseif ($commitment->getProposition()->getId() === $proposition2->getId()) {
                $this->assertEquals(CommitmentStatus::REFUSED, $commitment->getStatus());
                $this->assertEquals('Commentaire pour proposition refusée', $commitment->getCommentCandidateList());
            }
        }

        $this->assertEquals('Commentaire global de test', $candidateList->getGlobalComment());
    }

    public function testBatchCommitmentUpdateExistingStatus(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();

        // Créer des données de test avec un engagement existant
        $city = new City();
        $city->setName('Test City 2');
        $entityManager->persist($city);

        $candidateList = new CandidateList();
        $candidateList->setNameList('Test List 2');
        $candidateList->setFirstname('Jane');
        $candidateList->setLastname('Doe');
        $candidateList->setCity($city);
        $entityManager->persist($candidateList);

        $category = new Category();
        $category->setName('Test Category 2');
        $category->setPosition(1);
        $entityManager->persist($category);

        $proposition = new Proposition();
        $proposition->setTitle('Proposition Test');
        $proposition->setCategory($category);
        $entityManager->persist($proposition);

        // Créer un engagement existant avec statut "accepté"
        $existingCommitment = new Commitment();
        $existingCommitment->setCandidateList($candidateList);
        $existingCommitment->setProposition($proposition);
        $existingCommitment->setStatus(CommitmentStatus::ACCEPTED);
        $existingCommitment->setCommentCandidateList('Ancien commentaire');
        $entityManager->persist($existingCommitment);

        $entityManager->flush();

        // Mettre à jour le statut vers "refusé"
        $client->request('POST', '/admin', [
            'crudControllerFqcn' => 'App\\Controller\\Admin\\CandidateListCrudController',
            'crudAction' => 'batchCommitment',
            'entityId' => $candidateList->getId(),
        ], [], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ], http_build_query([
            'proposition_status' => [
                $proposition->getId() => 'refused',
            ],
            'proposition_comments' => [
                $proposition->getId() => 'Nouveau commentaire',
            ]
        ]));

        // Vérifier que l'engagement a été mis à jour
        $entityManager->refresh($existingCommitment);

        $this->assertEquals(CommitmentStatus::REFUSED, $existingCommitment->getStatus());
        $this->assertEquals('Nouveau commentaire', $existingCommitment->getCommentCandidateList());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (isset($this->entityManager)) {
            $this->entityManager->close();
        }
    }
}
